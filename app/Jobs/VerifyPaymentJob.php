<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\PaystackService;
use App\Services\MoniepointService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VerifyPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    public function __construct(
        public string $reference,
        public string $paymentMethod,
        public int $orderId
    ) {}

    public function handle(PaystackService $paystackService, MoniepointService $moniepointService): void
    {
        $order = Order::find($this->orderId);

        if (!$order) {
            Log::error("Order not found for payment verification: {$this->orderId}");
            return;
        }

        if ($order->payment_status === Order::PAYMENT_STATUS_PAID) {
            Log::info("Payment already verified for order: {$order->order_number}");
            return;
        }

        // Verify payment with appropriate service
        $service = $this->paymentMethod === 'paystack' ? $paystackService : $moniepointService;
        $verificationResult = $service->verifyPayment($this->reference);

        if ($verificationResult['status'] && $verificationResult['data']['status'] === 'success') {
            // Update order status
            $order->update([
                'payment_status' => Order::PAYMENT_STATUS_PAID,
                'payment_reference' => $this->reference,
                'paid_at' => now(),
                'status' => Order::STATUS_PROCESSING,
            ]);

            // Send confirmation emails
            \Mail::to($order->user)->queue(new \App\Mail\OrderCreated($order));
            \Mail::to(config('app.admin_email', 'admin@springglossy.com'))
                ->queue(new \App\Mail\AdminOrderNotification($order));

            Log::info("Payment verified successfully for order: {$order->order_number} via {$this->paymentMethod}");
        } else {
            Log::warning("Payment verification failed for order: {$order->order_number}", [
                'verification_result' => $verificationResult
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Payment verification job failed for order: {$this->orderId}", [
            'exception' => $exception->getMessage(),
            'reference' => $this->reference,
            'payment_method' => $this->paymentMethod
        ]);
    }
}
