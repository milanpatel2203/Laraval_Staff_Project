@extends('layouts.app')

@section('title', 'Edit Employee')
@section('page-title', 'Edit Employee')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">Edit Employee: {{ $employee->full_name }}</h2>
            <p class="text-xs text-gray-500 mt-0.5">Update employee profile, role, or compensation details.</p>
        </div>
        <a href="{{ route('employees.index') }}" class="px-4 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100 flex items-center gap-1.5">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    @if($errors->any())
    <div class="bg-white border border-gray-400 text-[#2D2D2D] px-4 py-3 rounded flex items-center gap-2.5 text-sm font-medium">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    <div class="bg-white border border-gray-200 rounded p-6">
        <form action="{{ route('employees.update', $employee->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Employee Code *</label>
                    <input type="text" name="employee_code" value="{{ old('employee_code', $employee->employee_code) }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                </div>

                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Status *</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                        <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="terminated" {{ old('status', $employee->status) == 'terminated' ? 'selected' : '' }}>Terminated</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">First Name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                </div>

                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Last Name *</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                </div>

                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email', $employee->email) }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                </div>

                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>

                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Department</label>
                    <select name="department_id" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }} ({{ $dept->code }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Access Role</label>
                    <select name="role_id" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        <option value="">Select Access Role</option>
                        @foreach($roles as $r)
                        <option value="{{ $r->id }}" {{ old('role_id', $employee->role_id) == $r->id ? 'selected' : '' }}>
                            {{ $r->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Designation / Role *</label>
                    <input type="text" name="designation" value="{{ old('designation', $employee->designation) }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                </div>

                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Date of Joining *</label>
                    <input type="date" name="joining_date" value="{{ old('joining_date', $employee->joining_date ? $employee->joining_date->format('Y-m-d') : '') }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                </div>

                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Monthly Salary (₹) *</label>
                    <input type="number" step="0.01" name="salary" value="{{ old('salary', $employee->salary) }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                </div>

                <div class="md:col-span-2">
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Residential Address</label>
                    <textarea name="address" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">{{ old('address', $employee->address) }}</textarea>
                </div>
            </div>

            <div class="mt-6 pt-5 border-t border-gray-200 flex justify-end gap-3 text-xs">
                <a href="{{ route('employees.index') }}" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-100">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-[#2D2D2D] text-white rounded font-semibold hover:bg-[#1a1a1a] flex items-center gap-1.5">
                    <i class="fas fa-save"></i> Update Employee
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
