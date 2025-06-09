<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AdminOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Order Received - ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-order-notification',
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        // Attach payment receipt if it's a bank transfer and receipt exists
        if ($this->order->payment_method === 'transfer' && $this->order->payment_receipt_path) {
            try {
                // Get the file from S3
                $fileContents = Storage::disk('s3')->get($this->order->payment_receipt_path);
                $fileName = 'payment_receipt_' . $this->order->order_number . '.' .
                           pathinfo($this->order->payment_receipt_path, PATHINFO_EXTENSION);

                $attachments[] = [
                    'data' => $fileContents,
                    'name' => $fileName,
                    'options' => [
                        'mime' => Storage::disk('s3')->mimeType($this->order->payment_receipt_path),
                    ],
                ];
            } catch (\Exception $e) {
                // Log error but don't fail email sending
                Log::error('Failed to attach receipt to admin email: ' . $e->getMessage());
            }
        }

        return $attachments;
    }
}
