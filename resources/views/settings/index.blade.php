@extends('layouts.app')

@section('title', 'System Settings')
@section('page-title', 'Settings')

@section('content')
    <div class="space-y-4">

        {{-- Top Header Bar --}}
        <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white border border-gray-200 rounded px-5 py-3 shadow-xs">
            <div>
                <h2 class="text-base font-bold text-[#2D2D2D] flex items-center gap-2">
                    <i class="fas fa-sliders-h text-sm text-[#2D2D2D]"></i>
                    System & Organization Settings
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Configure organization identity, work shifts, leave policies, and
                    payroll parameters.</p>
            </div>
            <div class="flex items-center gap-2 self-end sm:self-auto">
                <button type="submit" form="settingsForm"
                    class="px-4 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-black flex items-center gap-2 shadow-xs transition-colors cursor-pointer">
                    <i class="fas fa-save text-xs"></i>
                    <span>Save Settings</span>
                </button>
            </div>
        </div>

        {{-- Settings Form --}}
        <form id="settingsForm" action="{{ route('settings.update') }}" method="POST" class="space-y-4">
            @csrf

            {{-- 3-Column Settings Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-stretch">

                {{-- Column 1: Organization & Identity --}}
                <div class="bg-white border border-gray-200 rounded overflow-hidden flex flex-col shadow-xs">
                    <div class="px-4 py-3 bg-gray-50/70 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-[#2D2D2D] flex items-center gap-2">
                            <i class="fas fa-building text-gray-600"></i>
                            <span>Company Information</span>
                        </h3>
                        <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Identity</span>
                    </div>
                    <div class="p-4 space-y-3 text-xs flex-1">
                        <div>
                            <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Company Name *</label>
                            <input type="text" name="company_name" value="{{ $data['company_name'] }}" required
                                class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        </div>
                        <div>
                            <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Official Email *</label>
                            <input type="email" name="company_email" value="{{ $data['company_email'] }}" required
                                class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Phone Number</label>
                                <input type="text" name="company_phone" value="{{ $data['company_phone'] }}"
                                    class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                            </div>
                            <div>
                                <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Tax / GSTIN</label>
                                <input type="text" name="company_tax_id" value="{{ $data['company_tax_id'] ?? '' }}"
                                    placeholder="GSTIN / VAT ID"
                                    class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Website URL</label>
                            <input type="url" name="company_website" value="{{ $data['company_website'] ?? '' }}"
                                placeholder="https://example.com"
                                class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        </div>
                        <div>
                            <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Registered Address</label>
                            <textarea name="company_address" rows="3"
                                class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D] resize-none">{{ $data['company_address'] }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Column 2: Work Shift & Attendance Rules --}}
                <div class="bg-white border border-gray-200 rounded overflow-hidden flex flex-col shadow-xs">
                    <div class="px-4 py-3 bg-gray-50/70 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-[#2D2D2D] flex items-center gap-2">
                            <i class="fas fa-clock text-gray-600"></i>
                            <span>Work Shift & Attendance</span>
                        </h3>
                        <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Timings</span>
                    </div>
                    <div class="p-4 space-y-3 text-xs flex-1">
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Shift Start</label>
                                <input type="time" name="work_start_time" value="{{ $data['work_start_time'] }}"
                                    class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                            </div>
                            <div>
                                <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Shift End</label>
                                <input type="time" name="work_end_time" value="{{ $data['work_end_time'] }}"
                                    class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Working Days Schedule</label>
                            <select name="standard_working_days"
                                class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D] bg-white">
                                <option value="5" {{ $data['standard_working_days'] == '5' ? 'selected' : '' }}>5 Days (Monday
                                    – Friday)</option>
                                <option value="6" {{ $data['standard_working_days'] == '6' ? 'selected' : '' }}>6 Days (Monday
                                    – Saturday)</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Grace Period
                                    (Mins)</label>
                                <input type="number" min="0" max="60" name="attendance_grace_minutes"
                                    value="{{ $data['attendance_grace_minutes'] ?? '15' }}"
                                    class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                            </div>
                            <div>
                                <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Half-Day (Hours)</label>
                                <input type="number" step="0.5" min="2" max="6" name="half_day_hours"
                                    value="{{ $data['half_day_hours'] ?? '4.5' }}"
                                    class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Probation Period
                                (Months)</label>
                            <input type="number" min="1" max="12" name="probation_period_months"
                                value="{{ $data['probation_period_months'] ?? '3' }}"
                                class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        </div>
                        <div
                            class="p-2.5 bg-gray-50 border border-gray-200 rounded text-[11px] text-gray-500 leading-relaxed">
                            <i class="fas fa-info-circle text-[#2D2D2D] mr-1"></i> Punches recorded after grace period are
                            automatically marked as Late Attendance.
                        </div>
                    </div>
                </div>

                {{-- Column 3: Leaves & Payroll Policy --}}
                <div class="bg-white border border-gray-200 rounded overflow-hidden flex flex-col shadow-xs">
                    <div class="px-4 py-3 bg-gray-50/70 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-[#2D2D2D] flex items-center gap-2">
                            <i class="fas fa-calendar-check text-gray-600"></i>
                            <span>Leaves & Payroll Policy</span>
                        </h3>
                        <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Quotas</span>
                    </div>
                    <div class="p-4 space-y-3 text-xs flex-1">
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Annual Paid</label>
                                <input type="number" min="0" max="60" name="annual_leave_quota"
                                    value="{{ $data['annual_leave_quota'] }}"
                                    class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                            </div>
                            <div>
                                <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Sick Leave</label>
                                <input type="number" min="0" max="60" name="sick_leave_quota"
                                    value="{{ $data['sick_leave_quota'] }}"
                                    class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                            </div>
                            <div>
                                <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Casual</label>
                                <input type="number" min="0" max="60" name="casual_leave_quota"
                                    value="{{ $data['casual_leave_quota'] ?? '8' }}"
                                    class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Currency Symbol</label>
                                <input type="text" name="currency_symbol" value="{{ $data['currency_symbol'] }}"
                                    class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                            </div>
                            <div>
                                <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Monthly Pay Day</label>
                                <select name="payroll_cycle_date"
                                    class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D] bg-white">
                                    <option value="1" {{ ($data['payroll_cycle_date'] ?? '1') == '1' ? 'selected' : '' }}>1st
                                        of Month</option>
                                    <option value="5" {{ ($data['payroll_cycle_date'] ?? '1') == '5' ? 'selected' : '' }}>5th
                                        of Month</option>
                                    <option value="7" {{ ($data['payroll_cycle_date'] ?? '1') == '7' ? 'selected' : '' }}>7th
                                        of Month</option>
                                    <option value="28" {{ ($data['payroll_cycle_date'] ?? '1') == '28' ? 'selected' : '' }}>
                                        28th of Month</option>
                                    <option value="last" {{ ($data['payroll_cycle_date'] ?? '1') == 'last' ? 'selected' : '' }}>Last Day of Month</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Leave Approval
                                Workflow</label>
                            <select
                                class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D] bg-white">
                                <option value="manager">Department Manager & Super Admin Approval</option>
                                <option value="admin_only">Super Administrator Only</option>
                            </select>
                        </div>
                        <div
                            class="p-2.5 bg-gray-50 border border-gray-200 rounded text-[11px] text-gray-500 leading-relaxed">
                            <i class="fas fa-wallet text-[#2D2D2D] mr-1"></i> Payslips calculate standard salary using currency symbol and configured allowances.
                        </div>
                        <div class="pt-2 border-t border-gray-200 flex items-center justify-between">
                            <div>
                                <span class="block font-semibold text-[11px] text-[#2D2D2D]">Salary & Deduction Rates</span>
                                <span class="text-[10px] text-gray-500">Configure PF, PT, ESI, HRA, DA, etc.</span>
                            </div>
                            <a href="{{ route('payroll.configuration') }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded bg-[#2D2D2D] text-white hover:bg-black transition-colors shadow-sm">
                                <i class="fas fa-sliders-h text-[10px]"></i> Configure
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
@endsection