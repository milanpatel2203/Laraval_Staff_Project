<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Get the active user profile (authenticated or first existing user).
     */
    protected function getUser(): User
    {
        return Auth::user() ?? User::first() ?? User::create([
            'name' => 'Administrator',
            'email' => 'admin@uesthrms.com',
            'phone' => '+91 98765 43210',
            'role_title' => 'HR Manager',
            'bio' => 'Head of Human Resources and organizational operations.',
            'password' => Hash::make('admin123'),
        ]);
    }

    /**
     * Display the profile edit screen.
     */
    public function edit()
    {
        $user = $this->getUser();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = $this->getUser();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role_title' => 'required|string|max:100',
            'bio' => 'nullable|string|max:500',
        ]);

        $user->update($validated);

        ActivityLog::record(
            "Profile updated for {$user->name}",
            "Personal information and contact details updated",
            'user-edit'
        );

        return redirect()->route('profile.edit')->with('success', 'Profile information updated successfully.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $user = $this->getUser();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'The provided current password does not match our records.'])
                ->withInput();
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLog::record(
            "Password changed for {$user->name}",
            "Account security credentials updated",
            'lock'
        );

        return redirect()->route('profile.edit')->with('password_success', 'Account password updated successfully.');
    }
}
