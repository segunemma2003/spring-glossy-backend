<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\SendOrderConfirmationEmail;
use App\Jobs\SendAdminOrderNotification;

class SendOrderCreatedNotification
{
    public function handle(OrderCreated $event): void
    {
        // Send customer confirmation email
        SendOrderConfirmationEmail::dispatch($event->order);

        // Send admin notification
        SendAdminOrderNotification::dispatch($event->order);
    }
}
