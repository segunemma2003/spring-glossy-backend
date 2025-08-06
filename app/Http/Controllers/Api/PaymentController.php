<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    protected $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
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
     * Manual payment verification endpoint
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'reference' => 'required|string',
            'payment_method' => 'required|in:paystack,transfer'
        ]);

        $reference = $request->reference;
        $paymentMethod = $request->payment_method;

        $order = Order::where('order_number', $reference)
            ->orWhere('paystack_reference', $reference)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->status === 'paid') {
            return response()->json(['message' => 'Payment already verified', 'order' => $order]);
        }

        try {
            // Verify payment based on method
            $verificationResult = $paymentMethod === 'paystack'
                ? $this->paystackService->verifyPayment($reference)
                : null;

            if ($verificationResult && $verificationResult['status']) {
                return $this->handleSuccessfulPayment($reference, $paymentMethod);
            }

            return response()->json(['message' => 'Payment verification failed'], 400);

        } catch (\Exception $e) {
            Log::error('Payment verification error: ' . $e->getMessage());
            return response()->json(['message' => 'Payment verification error'], 500);
        }
    }

    /**
     * Handle successful payment
     */
    private function handleSuccessfulPayment($reference, $paymentMethod)
    {
        DB::beginTransaction();

        try {
            $order = Order::where('order_number', $reference)
                ->orWhere('paystack_reference', $reference)
                ->first();

            if (!$order) {
                throw new \Exception('Order not found');
            }

            if ($order->status === 'paid') {
                return response()->json(['message' => 'Payment already processed', 'order' => $order]);
            }

            // Update order status
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => $paymentMethod
            ]);

            // Send confirmation email
            Mail::to($order->user->email)->send(new \App\Mail\OrderCreated($order));

            // Send admin notification
            Mail::to(config('mail.admin_email', 'admin@example.com'))->send(new \App\Mail\AdminOrderNotification($order));

            DB::commit();

            return response()->json([
                'message' => 'Payment processed successfully',
                'order' => $order
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment processing error: ' . $e->getMessage());
            return response()->json(['message' => 'Payment processing error'], 500);
        }
    }

    /**
     * Get available payment methods
     */
    public function getPaymentMethods()
    {
        return response()->json([
            'payment_methods' => [
                [
                    'id' => 'paystack',
                    'name' => 'Paystack',
                    'description' => 'Pay with card or bank transfer',
                    'logo' => 'https://paystack.com/logo.png'
                ],
                [
                    'id' => 'transfer',
                    'name' => 'Bank Transfer',
                    'description' => 'Pay via bank transfer',
                    'logo' => 'https://example.com/bank-transfer.png'
                ]
            ]
        ]);
    }
}
