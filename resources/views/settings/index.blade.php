@extends('layouts.app')

@section('title', 'System Settings')
@section('page-title', 'Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    @if(session('success'))
    <div class="bg-white border border-[#2D2D2D] text-[#2D2D2D] px-4 py-3 rounded flex items-center justify-between text-sm font-medium">
        <div class="flex items-center gap-2.5">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-xs text-gray-500 hover:text-black">&times;</button>
    </div>
    @endif

    <div>
        <h2 class="text-lg font-bold text-[#2D2D2D]">System & Organization Settings</h2>
        <p class="text-xs text-gray-500 mt-0.5">Configure organization profile, shift timings, leave quotas, and operational parameters.</p>
    </div>

    <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Company Profile --}}
        <div class="bg-white border border-gray-200 rounded p-6">
            <div class="border-b border-gray-200 pb-3 mb-4">
                <h3 class="text-sm font-bold text-[#2D2D2D] flex items-center gap-2">
                    <i class="fas fa-building text-xs"></i> Company Information
                </h3>
                <p class="text-xs text-gray-500">Legal entity name and primary contact details for payslips and notifications.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Company Name *</label>
                    <input type="text" name="company_name" value="{{ $data['company_name'] }}" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Official Email Address *</label>
                    <input type="email" name="company_email" value="{{ $data['company_email'] }}" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Phone Number</label>
                    <input type="text" name="company_phone" value="{{ $data['company_phone'] }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Registered Address</label>
                    <input type="text" name="company_address" value="{{ $data['company_address'] }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
            </div>
        </div>

        {{-- Attendance & Shift Rules --}}
        <div class="bg-white border border-gray-200 rounded p-6">
            <div class="border-b border-gray-200 pb-3 mb-4">
                <h3 class="text-sm font-bold text-[#2D2D2D] flex items-center gap-2">
                    <i class="fas fa-clock text-xs"></i> Work Shift & Working Days
                </h3>
                <p class="text-xs text-gray-500">Configure official workday hours and schedule.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Shift Start Time</label>
                    <input type="time" name="work_start_time" value="{{ $data['work_start_time'] }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Shift End Time</label>
                    <input type="time" name="work_end_time" value="{{ $data['work_end_time'] }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Working Days Per Week</label>
                    <select name="standard_working_days" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        <option value="5" {{ $data['standard_working_days'] == '5' ? 'selected' : '' }}>5 Days (Mon - Fri)</option>
                        <option value="6" {{ $data['standard_working_days'] == '6' ? 'selected' : '' }}>6 Days (Mon - Sat)</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Leaves & Compensation Defaults --}}
        <div class="bg-white border border-gray-200 rounded p-6">
            <div class="border-b border-gray-200 pb-3 mb-4">
                <h3 class="text-sm font-bold text-[#2D2D2D] flex items-center gap-2">
                    <i class="fas fa-calendar-check text-xs"></i> Leave Quotas & Currency
                </h3>
                <p class="text-xs text-gray-500">Default annual entitlement per staff member and display currency.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Annual Paid Leaves (Days)</label>
                    <input type="number" name="annual_leave_quota" value="{{ $data['annual_leave_quota'] }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Sick Leaves (Days)</label>
                    <input type="number" name="sick_leave_quota" value="{{ $data['sick_leave_quota'] }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Currency Symbol</label>
                    <input type="text" name="currency_symbol" value="{{ $data['currency_symbol'] }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <button type="submit" class="px-5 py-2.5 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a] flex items-center gap-2">
                <i class="fas fa-save"></i> Save Settings
            </button>
        </div>
    </form>
</div>
@endsection
