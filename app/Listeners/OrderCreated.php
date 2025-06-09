<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Jobs\SendOrderConfirmationEmail;
use App\Jobs\SendAdminOrderNotification;

class OrderCreated
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {

        // Send customer confirmation email
        SendOrderConfirmationEmail::dispatch($event->order);

        // Send admin notification
        SendAdminOrderNotification::dispatch($event->order);

    }
}
