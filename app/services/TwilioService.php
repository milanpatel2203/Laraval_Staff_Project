<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    public function sendOtp(string $mobile, string $otp): void
    {
        $client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );

        $client->messages->create(
            $mobile,
            [
                'from' => config('services.twilio.from'),
                'body' => "Your HRMS OTP is: {$otp}. Do not share this OTP with anyone.",
            ]
        );
    }
}
