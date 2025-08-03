<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Handle contact form submission
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        try {
            // Send email to admin
            Mail::raw($this->formatContactEmail($request->all()), function ($message) use ($request) {
                $message->to('kemisolajim2018@gmail.com')
                        ->subject('New Contact Form Submission: ' . $request->subject)
                        ->replyTo($request->email, $request->first_name . ' ' . $request->last_name);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Your message has been sent successfully. We will get back to you soon!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send message. Please try again later.'
            ], 500);
        }
    }

    /**
     * Format the contact email content
     */
    private function formatContactEmail(array $data): string
    {
        return "
New Contact Form Submission

Name: {$data['first_name']} {$data['last_name']}
Email: {$data['email']}
Subject: {$data['subject']}

Message:
{$data['message']}

---
This message was sent from the Spring Glossy Cosmetics contact form.
        ";
    }
}
