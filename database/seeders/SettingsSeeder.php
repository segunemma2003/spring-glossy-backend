<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'caption' => 'Your Beauty, Our Passion',
            'instagram_link' => 'https://instagram.com/springglossy',
            'facebook_link' => 'https://facebook.com/springglossy',
            'twitter_link' => 'https://twitter.com/springglossy',
            'phone_number' => '+234 123 456 7890',
            'whatsapp_number' => '+234 123 456 7890',
            'official_email' => 'info@springglossy.com.ng',
            'address' => '123 Beauty Street, Lagos, Nigeria',
            'business_hours' => '<p><strong>Monday - Friday:</strong> 9:00 AM - 6:00 PM</p><p><strong>Saturday:</strong> 10:00 AM - 4:00 PM</p><p><strong>Sunday:</strong> Closed</p>',
            'my_story' => '<p>Welcome to Spring Glossy Cosmetics, where beauty meets quality. We are passionate about providing you with the finest cosmetic products that enhance your natural beauty.</p>',
            'privacy_policy' => '<h2>Privacy Policy</h2><p>This privacy policy describes how we collect, use, and protect your personal information.</p>',
            'terms_of_service' => '<h2>Terms of Service</h2><p>By using our services, you agree to these terms and conditions.</p>',
            'cookie_policy' => '<h2>Cookie Policy</h2><p>We use cookies to improve your browsing experience on our website.</p>',
        ];

        foreach ($settings as $key => $value) {
            Setting::setValue($key, $value);
        }
    }
}
