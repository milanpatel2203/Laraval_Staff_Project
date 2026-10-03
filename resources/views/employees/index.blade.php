@extends('layouts.app')

@section('title', 'Employees')
@section('page-title', 'Employees')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">Employees Directory ({{ $employees->total() }})</h2>
            <p class="text-xs text-gray-500 mt-0.5">Search, filter, and manage staff records.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('employees.export', request()->query()) }}" class="border border-gray-300 bg-white hover:bg-gray-50 text-[#2D2D2D] text-xs font-semibold px-3 py-2 rounded inline-flex items-center gap-1.5 shadow-sm transition">
                <i class="fas fa-file-excel text-emerald-600"></i> Export Excel
            </a>
            @if(auth()->user()->hasPermission('employees.create'))
            <a href="{{ route('employees.create') }}" class="bg-[#2D2D2D] hover:bg-[#1a1a1a] text-white text-xs font-semibold px-4 py-2 rounded inline-flex items-center gap-2">
                <i class="fas fa-user-plus"></i> Add Employee
            </a>
            @endif
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="bg-white border border-gray-200 rounded p-4">
        <form action="{{ route('employees.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="w-full sm:flex-1">
                <input type="text" name="search" value="{{ request('search') }}" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="Search by name, email, code or role...">
            </div>
            <div class="w-full sm:w-48">
                <select name="department_id" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-36">
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                </select>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="px-4 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a] flex items-center gap-1.5">
                    <i class="fas fa-search"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'department_id', 'status', 'filter']))
                <a href="{{ route('employees.index') }}" class="px-3 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- New Hires Active Filter Indicator --}}
    @if(request('filter') === 'new_hires')
    <div class="bg-white border border-[#2D2D2D] px-4 py-2.5 rounded flex items-center justify-between text-xs">
        <div class="flex items-center gap-2">
            <i class="fas fa-user-plus text-[#2D2D2D]"></i>
            <span class="font-semibold text-[#2D2D2D]">Filter Active: Showing employees joined within the last 30 days ({{ $employees->total() }})</span>
        </div>
        <a href="{{ route('employees.index') }}" class="font-semibold text-[#2D2D2D] underline">Clear Filter</a>
    </div>
    @endif

    {{-- Employees Table using Common Component --}}
    <x-table 
        :headers="[
            ['label' => 'Code', 'class' => 'w-24'],
            ['label' => 'Employee'],
            ['label' => 'Contact'],
            ['label' => 'Department'],
            ['label' => 'Designation'],
            ['label' => 'Joined'],
            ['label' => 'Salary'],
            ['label' => 'Status', 'class' => 'w-24'],
            ['label' => 'Actions', 'class' => 'w-24', 'align' => 'right']
        ]"
        :pagination="$employees"
        :empty="$employees->isEmpty()" 
        emptyMessage="No employees found.">
        @foreach($employees as $emp)
        <tr class="hover:bg-gray-50/50">
            <td class="py-3 px-4 font-bold text-[#2D2D2D]">{{ $emp->employee_code }}</td>
            <td class="py-3 px-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full overflow-hidden bg-[#2D2D2D] text-white flex items-center justify-center text-xs font-bold shrink-0 border border-gray-200">
                        @if($emp->avatar_url)
                            <img src="{{ $emp->avatar_url }}" alt="{{ $emp->full_name }}" class="w-full h-full object-cover">
                        @else
                            <span>{{ $emp->initials }}</span>
                        @endif
                    </div>
                    <span class="font-semibold text-[#2D2D2D]">{{ $emp->full_name }}</span>
                </div>
            </td>
            <td class="py-3 px-4">
                <div class="flex flex-col">
                    <span class="font-medium text-[#2D2D2D]">{{ $emp->email }}</span>
                    <span class="text-[11px] text-gray-500">{{ $emp->phone ?: '—' }}</span>
                </div>
            </td>
            <td class="py-3 px-4 text-[#2D2D2D]">{{ $emp->department ? $emp->department->name : 'Unassigned' }}</td>
            <td class="py-3 px-4 font-medium text-[#2D2D2D]">{{ $emp->designation }}</td>
            <td class="py-3 px-4 text-gray-600">{{ $emp->joining_date ? $emp->joining_date->format('d M Y') : '—' }}</td>
            <td class="py-3 px-4 font-medium text-[#2D2D2D]">₹{{ number_format($emp->salary, 2) }}</td>
            <td class="py-3 px-4">
                <x-badge :variant="$emp->status">{{ ucfirst($emp->status) }}</x-badge>
            </td>
            <td class="py-3 px-4 text-right">
                <div class="inline-flex items-center gap-1.5">
                    @if(auth()->user()->isSuperAdmin() && (!$emp->role || $emp->role->slug !== 'super-admin') && (auth()->check() && $emp->email !== auth()->user()->email))
                    <button type="button" onclick="openAssignRoleModal({{ $emp->id }}, '{{ addslashes($emp->full_name) }}', '{{ $emp->employee_code }}', {{ $emp->role_id ?? 'null' }})" class="btn-action-role text-xs" title="Assign Role (Super Admin)">
                        <i class="fas fa-user-shield"></i>
                    </button>
                    @endif
                    @if(auth()->user()->hasPermission('employees.edit'))
                    <a href="{{ route('employees.edit', $emp->id) }}" class="btn-action-edit text-xs" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('employees.delete'))
                    <form action="{{ route('employees.destroy', $emp->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this employee record?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-action-delete text-xs" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </td>
        </tr>
        @endforeach
    </x-table>
</div>

@if(auth()->user()->isSuperAdmin())
{{-- Quick Assign Role Modal (Super Admin) --}}
<div id="assignRoleModal" class="fixed inset-0 bg-[#2D2D2D]/60 flex items-center justify-center z-50 p-4" style="display: none;">
    <div class="bg-white rounded max-w-md w-full p-6 shadow-xl border border-gray-200">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-[#2D2D2D]">Assign Access Role</h3>
                    <p class="text-[11px] text-gray-500">Super Administrator Privileges</p>
                </div>
            </div>
            <button type="button" onclick="closeAssignRoleModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="assignRoleForm" onsubmit="submitAssignRole(event)" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" id="modal_employee_id" name="employee_id">

            <div class="p-3 bg-gray-50 rounded border border-gray-200 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-[#2D2D2D] block" id="modal_employee_name"></span>
                    <span class="text-[11px] text-gray-500 font-mono" id="modal_employee_code"></span>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-200 text-gray-700">Company Employee</span>
            </div>

            <div>
                <label class="block font-semibold text-xs text-[#2D2D2D] mb-1">Select Access Role</label>
                <select id="modal_role_id" name="role_id" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    <option value="">-- No Access Role (Unassigned) --</option>
                    @foreach($roles->where('slug', '!=', 'super-admin') as $r)
                    <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->slug }})</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-gray-500 mt-1">
                    Assigning a role grants all associated permissions and updates corresponding system user account.
                </p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeAssignRoleModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded text-xs font-semibold hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" id="btnSaveRole" class="px-4 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a] flex items-center gap-1.5">
                    <i class="fas fa-check"></i> Save Role Assignment
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openAssignRoleModal(id, name, code, roleId) {
        document.getElementById('modal_employee_id').value = id;
        document.getElementById('modal_employee_name').textContent = name;
        document.getElementById('modal_employee_code').textContent = code;
        document.getElementById('modal_role_id').value = roleId !== null && roleId !== undefined ? roleId : '';
        document.getElementById('assignRoleModal').style.display = 'flex';
    }

    function closeAssignRoleModal() {
        document.getElementById('assignRoleModal').style.display = 'none';
    }

    async function submitAssignRole(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSaveRole');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        const form = document.getElementById('assignRoleForm');
        const formData = new FormData(form);

        try {
            const res = await fetch("{{ route('roles.assignEmployee') }}", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });

            const data = await res.json();
            if (res.ok && data.success) {
                closeAssignRoleModal();
                if (window.Toast) {
                    Toast.fire({
                        icon: 'success',
                        title: data.message
                    });
                }
                setTimeout(() => window.location.reload(), 700);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Role Assignment Failed',
                    text: data.message || 'Could not assign role. Please try again.'
                });
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Network error occurred while saving role.'
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }
</script>
@endpush
@endif
@endsection
