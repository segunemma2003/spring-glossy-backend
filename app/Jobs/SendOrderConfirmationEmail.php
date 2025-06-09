<?php

namespace App\Jobs;

use App\Mail\OrderCreated;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
   public function __construct(public Order $order)
    {
    }

    public function handle(): void
    {
        Mail::to($this->order->user->email)
            ->send(new OrderCreated($this->order));
    }
}
