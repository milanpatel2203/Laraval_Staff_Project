<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class MobileOtpController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate([
            'mobile' => ['required', 'digits:10'],
        ]);

        $mobile = $request->mobile;

        // Check if mobile exists
        $user = User::where('mobile', $mobile)->first();

        if (! $user) {
            return back()->withErrors([
                'mobile' => 'No account found with this mobile number.',
            ])->withInput();
        }

        // Generate OTP
        $otp = random_int(100000, 999999);

        // Store OTP for 5 minutes
        session([
            'login_mobile' => $mobile,
            'login_otp' => $otp,
            'otp_expires_at' => now()->addMinutes(5),
        ]);

        return redirect()
            ->route('mobile.otp.verify')
            ->with('success', 'OTP generated successfully.');
    }

    public function showVerifyForm()
    {
        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        // Get OTP data from session
        $mobile = session('login_mobile');
        $savedOtp = session('login_otp');
        $expiresAt = session('otp_expires_at');

        // Check if OTP session exists
        if (! $mobile || ! $savedOtp || ! $expiresAt) {
            return redirect()
                ->route('mobile.login')
                ->withErrors([
                    'otp' => 'OTP expired. Please request a new OTP.',
                ]);
        }

        // Check OTP expiry
        if (now()->greaterThan($expiresAt)) {
            session()->forget([
                'login_otp',
                'otp_expires_at',
            ]);

            return back()->withErrors([
                'otp' => 'OTP expired. Please request a new OTP.',
            ]);
        }

        // Check OTP
        if ($request->otp != $savedOtp) {
            return back()->withErrors([
                'otp' => 'Invalid OTP. Please try again.',
            ]);
        }

        // Find user using mobile number
        $user = User::where('mobile', $mobile)->first();

        if (! $user) {
            return back()->withErrors([
                'otp' => 'No account found with this mobile number.',
            ]);
        }

        // Login user
        auth()->login($user);

        // Remove OTP data
        session()->forget([
            'login_mobile',
            'login_otp',
            'otp_expires_at',
        ]);

        // Redirect to dashboard
        return redirect()->route('dashboard');
    }
}
