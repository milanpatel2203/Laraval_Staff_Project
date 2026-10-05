<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Get the active user profile (authenticated or first existing user).
     */
    protected function getUser(): User
    {
        return Auth::user() ?? abort(401);
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
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'avatar_cropped' => 'nullable|string',
            'remove_avatar' => 'nullable|boolean',
        ]);

        if ($request->boolean('remove_avatar') && $user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $validated['avatar'] = null;
        } elseif (!empty($validated['avatar_cropped']) && str_starts_with($validated['avatar_cropped'], 'data:image/')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $imageParts = explode(';base64,', $validated['avatar_cropped']);
            if (isset($imageParts[1])) {
                $imageBase64 = base64_decode($imageParts[1]);
                $fileName = 'avatars/' . uniqid('avatar_') . '.jpg';
                Storage::disk('public')->put($fileName, $imageBase64);
                $validated['avatar'] = $fileName;
            }
        } elseif ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        unset($validated['avatar_cropped']);
        $user->update($validated);

        // Instantly synchronize avatar and profile details to linked employee record so Super Admin & colleagues see it immediately
        $targetAvatar = array_key_exists('avatar', $validated) ? $validated['avatar'] : $user->avatar;

        $linkedEmp = $user->linked_employee;
        if ($linkedEmp) {
            $empUpdate = ['avatar' => $targetAvatar];
            if (!empty($validated['phone'])) {
                $empUpdate['phone'] = $validated['phone'];
            }
            if (!empty($validated['name'])) {
                $nameParts = explode(' ', trim($validated['name']), 2);
                $empUpdate['first_name'] = $nameParts[0];
                if (isset($nameParts[1])) {
                    $empUpdate['last_name'] = $nameParts[1];
                }
            }
            $linkedEmp->update($empUpdate);
        }

        // Activity log

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

    /**
     * Update user theme preference.
     */
    public function updateTheme(Request $request)
    {
        $validated = $request->validate([
            'theme' => 'required|string|in:charcoal,navy,indigo,emerald,walnut,burgundy',
        ]);

        $user = $this->getUser();
        $user->update(['theme' => $validated['theme']]);

        return response()->json(['success' => true, 'theme' => $validated['theme']]);
    }
}
