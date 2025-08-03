<?php

namespace App\Console\Commands;

use App\Jobs\VerifyPaymentJob;
use App\Models\Order;
use Illuminate\Console\Command;

class VerifyPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:verify
                            {reference : Payment reference to verify}
                            {--method=paystack : Payment method (paystack, moniepoint, transfer)}
                            {--order-id= : Order ID to verify}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify payment for a specific order';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $reference = $this->argument('reference');
        $method = $this->option('method');
        $orderId = $this->option('order-id');

        if ($orderId) {
            $order = Order::find($orderId);
        } else {
            $order = Order::where('order_number', $reference)
                ->orWhere('paystack_reference', $reference)
                ->orWhere('moniepoint_reference', $reference)
                ->first();
        }

        if (!$order) {
            $this->error('Order not found');
            return 1;
        }

        $this->info("Verifying payment for order: {$order->order_number}");
        $this->info("Payment method: {$method}");
        $this->info("Reference: {$reference}");

        if ($method === 'transfer') {
            $this->warn('Bank transfer verification requires manual admin review');
            $this->info("Order status: {$order->status}");
            $this->info("Payment status: {$order->payment_status}");
            return 0;
        }

        // Dispatch verification job
        VerifyPaymentJob::dispatch($reference, $method, $order->id);

        $this->info('Payment verification job dispatched successfully');
        return 0;
    }
}
