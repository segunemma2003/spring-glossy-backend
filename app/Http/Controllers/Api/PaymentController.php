<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaystackService;
use App\Services\MonnifyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    protected $paystackService;
    protected $monnifyService;

    public function __construct(PaystackService $paystackService, MonnifyService $monnifyService)
    {
        $this->paystackService = $paystackService;
        $this->monnifyService = $monnifyService;
    }

    /**
     * Paystack webhook handler
     */
    public function paystackWebhook(Request $request)
    {
        Log::info('Paystack webhook received', $request->all());

        $payload = $request->getContent();
        $signature = $request->header('X-Paystack-Signature');

        // Verify webhook signature
        $expectedSignature = hash_hmac('sha512', $payload, config('services.paystack.secret_key'));

        if (!hash_equals($expectedSignature, $signature)) {
            Log::error('Paystack webhook signature verification failed');
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $data = $request->all();

        if ($data['event'] === 'charge.success') {
            return $this->handleSuccessfulPayment($data['data']['reference'], 'paystack');
        }

        return response()->json(['message' => 'Webhook processed']);
    }

    /**
     * Monnify webhook handler
     */
    public function monnifyWebhook(Request $request)
    {
        Log::info('Monnify webhook received', $request->all());

        $payload = $request->getContent();
        $signature = $request->header('MNFY-SIGNATURE');

        // Verify webhook signature
        if (!$this->monnifyService->verifyWebhookSignature($payload, $signature)) {
            Log::error('Monnify webhook signature verification failed');
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $data = $request->all();

        // Check if payment is successful
        if ($data['paymentStatus'] === 'PAID') {
            return $this->handleSuccessfulPayment($data['paymentReference'], 'monnify');
        }

        // Log other payment statuses for monitoring
        Log::info('Monnify payment status: ' . $data['paymentStatus'], [
            'reference' => $data['paymentReference'],
            'status' => $data['paymentStatus']
        ]);

        return response()->json(['message' => 'Webhook processed']);
    }

    /**
     * Manual payment verification endpoint
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'reference' => 'required|string',
            'payment_method' => 'required|in:paystack,monnify,transfer'
        ]);

        $reference = $request->reference;
        $paymentMethod = $request->payment_method;

        $order = Order::where('order_number', $reference)
            ->orWhere('paystack_reference', $reference)
            ->orWhere('monnify_reference', $reference)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($paymentMethod === 'transfer') {
            // For bank transfer, just return the order status
            return response()->json([
                'order' => $order->load('items.product'),
                'payment_status' => $order->payment_status,
                'message' => 'Bank transfer verification requires manual admin review'
            ]);
        }

        // Verify payment with payment gateway
        $verificationResult = $paymentMethod === 'paystack'
            ? $this->paystackService->verifyPayment($reference)
            : $this->monnifyService->verifyPayment($reference);

        if ($verificationResult['status'] && $verificationResult['data']['status'] === 'success') {
            return $this->handleSuccessfulPayment($reference, $paymentMethod);
        }

        return response()->json([
            'order' => $order->load('items.product'),
            'payment_status' => $order->payment_status,
            'verification_result' => $verificationResult,
            'message' => 'Payment verification failed'
        ]);
    }

    /**
     * Handle successful payment
     */
    private function handleSuccessfulPayment($reference, $paymentMethod)
    {
        return DB::transaction(function () use ($reference, $paymentMethod) {
            $order = Order::where('order_number', $reference)
                ->orWhere('paystack_reference', $reference)
                ->orWhere('monnify_reference', $reference)
                ->first();

            if (!$order) {
                Log::error("Order not found for reference: {$reference}");
                return response()->json(['message' => 'Order not found'], 404);
            }

            if ($order->payment_status === Order::PAYMENT_STATUS_PAID) {
                return response()->json([
                    'message' => 'Payment already processed',
                    'order' => $order->load('items.product')
                ]);
            }

            // Update order payment status
            $order->update([
                'payment_status' => Order::PAYMENT_STATUS_PAID,
                'payment_reference' => $reference,
                'paid_at' => now(),
                'status' => Order::STATUS_PROCESSING,
            ]);

            // Send confirmation emails
            Mail::to($order->user)->queue(new \App\Mail\OrderCreated($order));
            Mail::to(config('app.admin_email', 'kemisolajim2018@gmail.com'))
                ->queue(new \App\Mail\AdminOrderNotification($order));

            Log::info("Payment successful for order: {$order->order_number} via {$paymentMethod}");

            return response()->json([
                'message' => 'Payment processed successfully',
                'order' => $order->load('items.product')
            ]);
        });
    }

    /**
     * Get payment methods available
     */
    public function getPaymentMethods()
    {
        return response()->json([
            'payment_methods' => [
                [
                    'id' => 'paystack',
                    'name' => 'Paystack',
                    'description' => 'Pay with card, bank transfer, USSD, etc.',
                    'logo' => 'https://paystack.com/static/img/logo-blue.svg'
                ],
                [
                    'id' => 'monnify',
                    'name' => 'Monnify',
                    'description' => 'Pay with Monnify wallet or card',
                    'logo' => 'https://monnify.com/logo.png'
                ],
                [
                    'id' => 'transfer',
                    'name' => 'Bank Transfer',
                    'description' => 'Pay via bank transfer and upload receipt',
                    'logo' => null
                ]
            ]
        ]);
    }
}
