<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Temporary test route for email configuration
Route::get('/test-email', function () {
    try {
        Mail::raw('This is a test email from Spring Glossy Cosmetics to verify mail configuration is working correctly.', function ($message) {
            $message->to('segunemma2003@gmail.com')
                    ->subject('Test Email - Spring Glossy Mail Configuration');
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Test email sent successfully to segunemma2003@gmail.com',
            'from' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'mailer' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to send test email',
            'error' => $e->getMessage(),
            'from' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'mailer' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
        ], 500);
    }
});
