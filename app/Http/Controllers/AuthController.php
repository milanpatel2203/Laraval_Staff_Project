<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    // Show Login Page
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        if ($request->has('clear_otp')) {
            session()->forget(['login_otp', 'login_mobile', 'otp_expires_at']);
            return redirect()->route('login');
        }

        return view('auth.login');
    }

    // Email Login
    public function emailLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        return back()
            ->withErrors([
                'email' => 'Invalid email or password.',
            ])
            ->withInput();
    }

    // Send Mobile OTP
    public function sendOtp(Request $request)
    {
        $request->validate([
            'mobile' => 'required|digits:10',
        ]);

        $user = User::where('mobile', $request->mobile)->first();

        if (! $user) {
            return back()
                ->withErrors([
                    'mobile' => 'Mobile number is not registered.',
                ])
                ->withInput();
        }

        // Temporary OTP for testing
        $otp = rand(100000, 999999);

        session([
            'login_otp' => $otp,
            'login_mobile' => $request->mobile,
            'otp_expires_at' => now()->addMinutes(5),
        ]);

        return back()->with(
            'otp_sent',
            "OTP generated: $otp"
        );
    }

    // Verify Mobile OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'mobile' => 'required|digits:10',
            'otp' => 'required|digits:6',
        ]);

        if (
            session('login_mobile') !== $request->mobile ||
            session('login_otp') != $request->otp ||
            now()->greaterThan(session('otp_expires_at'))
        ) {
            return back()
                ->withErrors([
                    'otp' => 'Invalid or expired OTP.',
                ])
                ->withInput();
        }

        $user = User::where('mobile', $request->mobile)->first();

        if (! $user) {
            return back()
                ->withErrors([
                    'mobile' => 'User not found.',
                ])
                ->withInput();
        }

        Auth::login($user);

        $request->session()->regenerate();

        session()->forget([
            'login_otp',
            'login_mobile',
            'otp_expires_at',
        ]);

        return redirect()->route('dashboard');
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // Forgot Password
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with(
                'success',
                'Password reset link has been sent successfully.'
            );
        }

        return back()
            ->withErrors([
                'email' => __($status),
            ])
            ->withInput();
    }

    // Reset Password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            function ($user, $password) {

                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {

            return redirect()
                ->route('login')
                ->with(
                    'success',
                    'Password reset successfully. Please login.'
                );
        }

        return back()->withErrors([
            'email' => __($status),
        ]);
    }
}
