<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Get all settings
     */
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        return response()->json([
            'status' => 'success',
            'data' => [
                'caption' => $settings['caption'] ?? '',
                'instagram_link' => $settings['instagram_link'] ?? '',
                'facebook_link' => $settings['facebook_link'] ?? '',
                'twitter_link' => $settings['twitter_link'] ?? '',
                'phone_number' => $settings['phone_number'] ?? '',
                'whatsapp_number' => $settings['whatsapp_number'] ?? '',
                'official_email' => $settings['official_email'] ?? '',
                'address' => $settings['address'] ?? '',
                'location' => $settings['location'] ?? '',
                'business_hours' => $settings['business_hours'] ?? '',
                'my_story' => $settings['my_story'] ?? '',
                'privacy_policy' => $settings['privacy_policy'] ?? '',
                'terms_of_service' => $settings['terms_of_service'] ?? '',
                'cookie_policy' => $settings['cookie_policy'] ?? '',
            ]
        ]);
    }

    /**
     * Get a specific setting by key
     */
    public function show($key)
    {
        $value = Setting::getValue($key);

        if ($value === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Setting not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'key' => $key,
                'value' => $value
            ]
        ]);
    }
}
