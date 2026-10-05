@extends('layouts.app')

@section('title', 'Add Employee')
@section('page-title', 'Add Employee')

@section('content')
<div class="max-w-5xl mx-auto space-y-3">
    {{-- Top Header Bar with Instant Save --}}
    <div class="flex items-center justify-between bg-white border border-gray-200 rounded px-4 py-2 shadow-xs">
        <div>
            <h2 class="text-sm font-bold text-[#2D2D2D]">New Employee Registration</h2>
            <p class="text-[11px] text-gray-500">Fill in details to register employee record and credentials.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('employees.index') }}" class="px-3 py-1.5 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100 flex items-center gap-1.5">
                <i class="fas fa-arrow-left text-[10px]"></i> Back to List
            </a>
            <button type="submit" form="createEmployeeForm" class="px-4 py-1.5 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-black flex items-center gap-1.5 shadow-sm">
                <i class="fas fa-save text-[11px]"></i> Save Employee
            </button>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-white border border-red-300 text-red-700 px-3.5 py-2 rounded flex items-center gap-2 text-xs font-medium">
        <i class="fas fa-exclamation-circle text-sm text-red-600"></i>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    <div class="bg-white border border-gray-200 rounded p-4 shadow-xs">
        <form id="createEmployeeForm" action="{{ route('employees.store') }}" method="POST" class="space-y-3">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-2.5 text-xs">
                {{-- Row 1: Core Identification --}}
                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Employee Code *</label>
                    <input type="text" name="employee_code" value="{{ old('employee_code', $suggestedCode) }}" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                </div>

                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Employment Status *</label>
                    <select name="status" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D] bg-white" required>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="terminated" {{ old('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Designation / Job Title *</label>
                    <input type="text" name="designation" value="{{ old('designation') }}" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required placeholder="e.g. Frontend Developer">
                </div>

                {{-- Row 2: Personal & Contact --}}
                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">First Name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required placeholder="e.g. Rahul">
                </div>

                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Last Name *</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required placeholder="e.g. Sharma">
                </div>

                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required placeholder="name@company.com">
                </div>

                {{-- Row 3: Organizational Placement --}}
                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="+91 98765 43210">
                </div>

                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Department</label>
                    <select name="department_id" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D] bg-white">
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }} ({{ $dept->code }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Access Role</label>
                    @if(auth()->user()->isSuperAdmin())
                    <select name="role_id" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D] bg-white">
                        <option value="">Select Access Role</option>
                        @foreach($roles as $r)
                        <option value="{{ $r->id }}" {{ old('role_id') == $r->id ? 'selected' : '' }}>
                            {{ $r->name }}
                        </option>
                        @endforeach
                    </select>
                    @else
                    <div class="px-2.5 py-1.5 bg-gray-100 border border-gray-200 rounded text-xs text-gray-500 flex items-center justify-between">
                        <span>Staff / Employee</span>
                        <span class="text-[10px] text-gray-400 font-medium"><i class="fas fa-lock text-[9px] mr-1"></i> Super Admin only</span>
                    </div>
                    @endif
                </div>

                {{-- Row 4: Compensation & Joining & Address --}}
                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Date of Joining *</label>
                    <input type="date" name="joining_date" value="{{ old('joining_date', date('Y-m-d')) }}" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                </div>

                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Monthly Salary (₹) *</label>
                    <input type="number" step="0.01" name="salary" value="{{ old('salary', '0.00') }}" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required placeholder="50000.00">
                </div>

                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Residential Address</label>
                    <input type="text" name="address" value="{{ old('address') }}" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="City, State, Country...">
                </div>
            </div>

            <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                <span class="text-[11px] text-gray-400"><i class="fas fa-info-circle text-gray-400 mr-1"></i> A login account will be linked to the official email</span>
                <div class="flex items-center gap-2">
                    <a href="{{ route('employees.index') }}" class="px-3 py-1.5 border border-gray-300 rounded font-medium text-xs text-gray-700 bg-white hover:bg-gray-100">Cancel</a>
                    <button type="submit" class="px-4 py-1.5 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-black flex items-center gap-1.5 shadow-sm">
                        <i class="fas fa-save text-[11px]"></i> Save Employee
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
