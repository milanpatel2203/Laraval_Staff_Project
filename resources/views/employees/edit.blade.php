@extends('layouts.app')

@section('title', 'Edit Employee')
@section('page-title', 'Edit Employee')

@section('content')
<div class="max-w-5xl mx-auto space-y-3">
    {{-- Top Header Bar with Instant Save --}}
    <div class="flex items-center justify-between bg-white border border-gray-200 rounded px-4 py-2 shadow-xs">
        <div>
            <h2 class="text-sm font-bold text-[#2D2D2D]">Edit Employee: {{ $employee->full_name }}</h2>
            <p class="text-[11px] text-gray-500">Update employee profile, compensation, status, or organizational role.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('employees.index') }}" class="px-3 py-1.5 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100 flex items-center gap-1.5">
                <i class="fas fa-arrow-left text-[10px]"></i> Back to List
            </a>
            <button type="submit" form="editEmployeeForm" class="px-4 py-1.5 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-black flex items-center gap-1.5 shadow-sm">
                <i class="fas fa-save text-[11px]"></i> Update Employee
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
        <form id="editEmployeeForm" action="{{ route('employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            {{-- Profile Photo Section --}}
            <div class="flex items-center gap-4 p-3 bg-gray-50 border border-gray-200 rounded">
                <div class="w-14 h-14 rounded-full overflow-hidden bg-[#2D2D2D] text-white flex items-center justify-center text-sm font-bold shrink-0 border-2 border-white shadow-sm">
                    @if($employee->avatar_url)
                        <img id="empAvatarPreview" src="{{ $employee->avatar_url }}" alt="{{ $employee->full_name }}" class="w-full h-full object-cover">
                    @else
                        <span id="empAvatarFallback">{{ $employee->initials }}</span>
                        <img id="empAvatarPreview" src="" alt="{{ $employee->full_name }}" class="w-full h-full object-cover hidden">
                    @endif
                </div>
                <div class="space-y-1">
                    <span class="block text-xs font-bold text-[#2D2D2D]">Profile Photo</span>
                    <p class="text-[11px] text-gray-500">Visible across HRMS directory, leaves, attendance, and dashboard.</p>
                    <div class="flex items-center gap-2 pt-1">
                        <label for="empAvatarInput" class="cursor-pointer px-2.5 py-1 bg-white border border-gray-300 rounded font-semibold text-[11px] text-[#2D2D2D] hover:bg-gray-100 flex items-center gap-1.5 transition">
                            <i class="fas fa-camera text-gray-500"></i> Change Photo
                        </label>
                        <input type="file" name="avatar" id="empAvatarInput" accept="image/*" class="hidden" onchange="previewEmpAvatar(event)">
                        @if($employee->avatar)
                        <label class="flex items-center gap-1 text-[11px] text-red-600 cursor-pointer ml-2">
                            <input type="checkbox" name="remove_avatar" value="1" class="rounded text-red-600 focus:ring-0 w-3 h-3">
                            <span>Remove Photo</span>
                        </label>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-2.5 text-xs">
                {{-- Row 1: Core Identification --}}
                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Employee Code *</label>
                    <input type="text" name="employee_code" value="{{ old('employee_code', $employee->employee_code) }}" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                </div>

                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Employment Status *</label>
                    <select name="status" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D] bg-white" required>
                        <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="terminated" {{ old('status', $employee->status) == 'terminated' ? 'selected' : '' }}>Terminated</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Designation / Job Title *</label>
                    <input type="text" name="designation" value="{{ old('designation', $employee->designation) }}" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                </div>

                {{-- Row 2: Personal & Contact --}}
                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">First Name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                </div>

                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Last Name *</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                </div>

                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email', $employee->email) }}" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                </div>

                {{-- Row 3: Organizational Placement --}}
                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>

                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Department</label>
                    <select name="department_id" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D] bg-white">
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>
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
                        <option value="{{ $r->id }}" {{ old('role_id', $employee->role_id) == $r->id ? 'selected' : '' }}>
                            {{ $r->name }}
                        </option>
                        @endforeach
                    </select>
                    @else
                    <div class="px-2.5 py-1.5 bg-gray-100 border border-gray-200 rounded text-xs text-gray-700 flex items-center justify-between">
                        <span class="font-medium">{{ $employee->role ? $employee->role->name : 'Unassigned' }}</span>
                        <span class="text-[10px] text-gray-400 font-medium"><i class="fas fa-lock text-[9px] mr-1"></i> Super Admin only</span>
                    </div>
                    @endif
                </div>

                {{-- Row 4: Compensation & Joining & Address --}}
                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Date of Joining *</label>
                    <input type="date" name="joining_date" value="{{ old('joining_date', $employee->joining_date ? $employee->joining_date->format('Y-m-d') : '') }}" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                </div>

                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Monthly Salary (₹) *</label>
                    <input type="number" step="0.01" name="salary" value="{{ old('salary', $employee->salary) }}" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                </div>

                <div>
                    <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Residential Address</label>
                    <input type="text" name="address" value="{{ old('address', $employee->address) }}" class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="City, State, Country...">
                </div>
            </div>

            <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                <span class="text-[11px] text-gray-400"><i class="fas fa-check-circle text-emerald-500 mr-1"></i> Changes save directly to employee profile</span>
                <div class="flex items-center gap-2">
                    <a href="{{ route('employees.index') }}" class="px-3 py-1.5 border border-gray-300 rounded font-medium text-xs text-gray-700 bg-white hover:bg-gray-100">Cancel</a>
                    <button type="submit" class="px-4 py-1.5 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-black flex items-center gap-1.5 shadow-sm">
                        <i class="fas fa-save text-[11px]"></i> Update Employee
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewEmpAvatar(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('empAvatarPreview');
            const fallback = document.getElementById('empAvatarFallback');
            if (preview) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            }
            if (fallback) fallback.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endpush
