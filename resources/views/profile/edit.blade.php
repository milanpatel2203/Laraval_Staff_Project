@extends('layouts.app')

@section('title', 'Manage Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Success Flash Notifications --}}
    @if(session('success'))
    <div class="bg-white border border-[#2D2D2D] text-[#2D2D2D] px-4 py-3 rounded flex items-center justify-between text-sm font-medium">
        <div class="flex items-center gap-2.5">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-xs text-gray-500 hover:text-black">&times;</button>
    </div>
    @endif

    @if(session('password_success'))
    <div class="bg-white border border-[#2D2D2D] text-[#2D2D2D] px-4 py-3 rounded flex items-center justify-between text-sm font-medium">
        <div class="flex items-center gap-2.5">
            <i class="fas fa-key"></i>
            <span>{{ session('password_success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-xs text-gray-500 hover:text-black">&times;</button>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-white border border-gray-400 text-[#2D2D2D] px-4 py-3 rounded flex items-center justify-between text-sm font-medium">
        <div class="flex items-center gap-2.5">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ $errors->first() }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-xs text-gray-500 hover:text-black">&times;</button>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">Account & Profile Management</h2>
            <p class="text-xs text-gray-500 mt-0.5">Manage your personal information, role details, and security credentials.</p>
        </div>
    </div>

    {{-- Profile Overview Card --}}
    <div class="bg-white border border-gray-200 rounded p-6 flex flex-col sm:flex-row items-center sm:items-start gap-6">
        <div class="w-20 h-20 rounded-full bg-[#2D2D2D] text-[#F5F5F5] flex items-center justify-center text-2xl font-bold shrink-0">
            {{ $user->initials }}
        </div>
        <div class="flex-1 text-center sm:text-left space-y-1">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                <h3 class="text-base font-bold text-[#2D2D2D]">{{ $user->name }}</h3>
                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-[#2D2D2D] text-white uppercase tracking-wider self-center sm:self-auto">
                    {{ $user->role_title }}
                </span>
            </div>
            <p class="text-xs text-gray-500">{{ $user->email }} &middot; {{ $user->phone ?: 'No phone registered' }}</p>
            @if($user->bio)
            <p class="text-xs text-gray-600 mt-2">{{ $user->bio }}</p>
            @endif
        </div>
        <div class="text-xs text-gray-400 shrink-0 text-center sm:text-right">
            <span>Account Active</span>
            <span class="block text-[11px] text-gray-500 mt-1">Joined {{ $user->created_at ? $user->created_at->format('M Y') : 'Oct 2026' }}</span>
        </div>
    </div>

    {{-- Form Grid: Personal Info & Password Change --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- 1. Edit Personal Information --}}
        <div class="bg-white border border-gray-200 rounded p-6 flex flex-col justify-between">
            <div>
                <div class="border-b border-gray-200 pb-3 mb-4">
                    <h3 class="text-sm font-bold text-[#2D2D2D] flex items-center gap-2">
                        <i class="fas fa-user-edit text-xs"></i> Personal Information
                    </h3>
                    <p class="text-xs text-gray-500">Update your public details and contact information.</p>
                </div>

                <form id="profileForm" action="{{ route('profile.update') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-semibold text-[#2D2D2D] mb-1">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    </div>

                    <div>
                        <label class="block font-semibold text-[#2D2D2D] mb-1">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    </div>

                    <div>
                        <label class="block font-semibold text-[#2D2D2D] mb-1">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+91 98765 43210" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    </div>

                    <div>
                        <label class="block font-semibold text-[#2D2D2D] mb-1">Role / Job Title *</label>
                        <input type="text" name="role_title" value="{{ old('role_title', $user->role_title) }}" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    </div>

                    <div>
                        <label class="block font-semibold text-[#2D2D2D] mb-1">Bio / Notes</label>
                        <textarea name="bio" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="Short personal description...">{{ old('bio', $user->bio) }}</textarea>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-5 py-2 bg-[#2D2D2D] text-white rounded font-semibold text-xs hover:bg-[#1a1a1a] flex items-center gap-1.5">
                            <i class="fas fa-save"></i> Save Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 2. Change Account Password --}}
        <div class="bg-white border border-gray-200 rounded p-6 flex flex-col justify-between">
            <div>
                <div class="border-b border-gray-200 pb-3 mb-4">
                    <h3 class="text-sm font-bold text-[#2D2D2D] flex items-center gap-2">
                        <i class="fas fa-shield-alt text-xs"></i> Security & Password
                    </h3>
                    <p class="text-xs text-gray-500">Ensure your account uses a secure password.</p>
                </div>

                <form action="{{ route('profile.password') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-semibold text-[#2D2D2D] mb-1">Current Password *</label>
                        <input type="password" name="current_password" required placeholder="Enter current password" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    </div>

                    <div>
                        <label class="block font-semibold text-[#2D2D2D] mb-1">New Password *</label>
                        <input type="password" name="password" required placeholder="At least 6 characters" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    </div>

                    <div>
                        <label class="block font-semibold text-[#2D2D2D] mb-1">Confirm New Password *</label>
                        <input type="password" name="password_confirmation" required placeholder="Repeat new password" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    </div>

                    <div class="p-3 bg-gray-50 border border-gray-200 rounded text-[11px] text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i> Default password was set to <code>admin123</code>. You can change it anytime here.
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-5 py-2 bg-[#2D2D2D] text-white rounded font-semibold text-xs hover:bg-[#1a1a1a] flex items-center gap-1.5">
                            <i class="fas fa-key"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
