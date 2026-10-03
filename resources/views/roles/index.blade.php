@extends('layouts.app')

@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">Access Control Roles ({{ $roles->total() }})</h2>
            <p class="text-xs text-gray-500 mt-0.5">Define access roles and manage granular permissions across all HRMS modules.</p>
        </div>
        <a href="{{ route('roles.create') }}" class="px-4 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a] flex items-center gap-1.5">
            <i class="fas fa-plus"></i> Create New Role
        </a>
    </div>

    {{-- Roles Cards / Table --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($roles as $role)
        <div class="bg-white border border-gray-200 rounded p-5 flex flex-col justify-between space-y-4 hover:border-[#2D2D2D] transition-none">
            <div>
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded bg-[#2D2D2D] text-[#F5F5F5] flex items-center justify-center text-sm shrink-0">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-[#2D2D2D]">{{ $role->name }}</h3>
                            <span class="text-[11px] text-gray-400 font-mono">{{ $role->slug }}</span>
                        </div>
                    </div>
                    @if($role->is_system)
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-gray-200 text-gray-700">
                        System Role
                    </span>
                    @endif
                </div>

                <p class="text-xs text-gray-600 mt-3 leading-relaxed">
                    {{ $role->description ?: 'No specific description provided for this role.' }}
                </p>
            </div>

            <div>
                <div class="py-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                    <div class="flex items-center gap-1.5">
                        <i class="fas fa-users text-gray-400"></i>
                        <span><strong>{{ $role->employees_count }}</strong> assigned staff</span>
                        @if(auth()->user()->isSuperAdmin() && !$role->is_system && $role->slug !== 'super-admin')
                        <button type="button" onclick="openAssignModal({{ $role->id }})" class="ml-2 px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 hover:bg-blue-100 text-[10px] font-semibold flex items-center gap-1 transition-colors" title="Assign employees to this role">
                            <i class="fas fa-user-plus text-[9px]"></i> Assign Staff
                        </button>
                        @endif
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i class="fas fa-key text-gray-400"></i>
                        <span><strong>{{ $role->permissions_count }}</strong> of {{ $totalPermissions }} permissions</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                    <div class="flex flex-wrap gap-1">
                        @foreach($role->permissions->take(3) as $perm)
                        <span class="px-1.5 py-0.5 bg-gray-100 rounded text-[10px] text-gray-600">
                            {{ $perm->name }}
                        </span>
                        @endforeach
                        @if($role->permissions->count() > 3)
                        <span class="px-1.5 py-0.5 bg-gray-100 rounded text-[10px] font-semibold text-gray-500">
                            +{{ $role->permissions->count() - 3 }} more
                        </span>
                        @endif
                    </div>

                    <div class="inline-flex items-center gap-1.5 shrink-0 ml-2">
                        <a href="{{ route('roles.edit', $role->id) }}" class="btn-action-edit text-xs" title="Edit Role & Permissions">
                            <i class="fas fa-edit"></i>
                        </a>
                        @if(!$role->is_system)
                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete role {{ $role->name }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action-delete text-xs" title="Delete Role">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-2 bg-white border border-gray-200 rounded p-8 text-center text-gray-400">
            No roles configured. Click "Create New Role" above.
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($roles->hasPages())
    <div class="bg-white border border-gray-200 rounded px-4 py-3 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
        <span class="text-gray-500">
            Showing <span class="font-bold text-[#2D2D2D]">{{ $roles->firstItem() }}</span> to <span class="font-bold text-[#2D2D2D]">{{ $roles->lastItem() }}</span> of <span class="font-bold text-[#2D2D2D]">{{ $roles->total() }}</span> records
        </span>
        <div class="custom-pagination">
            {{ $roles->links() }}
        </div>
    </div>
    @endif
</div>

@if(auth()->user()->isSuperAdmin())
{{-- Super Admin Modal: Assign Roles of All Company's Employees --}}
<div id="companyRoleAssignModal" class="fixed inset-0 bg-[#2D2D2D]/60 flex items-center justify-center z-50 p-4" style="display: none;">
    <div class="bg-white rounded max-w-4xl w-full max-h-[90vh] flex flex-col shadow-2xl border border-gray-200">
        {{-- Modal Header --}}
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-[#2D2D2D]">Assign Roles to Company Employees</h3>
                    <p class="text-xs text-gray-500">Super Administrator access control & privilege management</p>
                </div>
            </div>
            <button type="button" onclick="closeAssignModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <i class="fas fa-times text-base"></i>
            </button>
        </div>

        {{-- Tabs & Filters Bar --}}
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3 shrink-0">
            <div class="flex items-center gap-2">
                <button type="button" id="tabBtnIndividual" onclick="switchAssignTab('individual')" class="px-3 py-1.5 rounded text-xs font-semibold bg-[#2D2D2D] text-white">
                    <i class="fas fa-list mr-1"></i> All Employees ({{ $employees->count() }})
                </button>
                <button type="button" id="tabBtnBatch" onclick="switchAssignTab('batch')" class="px-3 py-1.5 rounded text-xs font-semibold bg-white border border-gray-300 text-gray-700 hover:bg-gray-100">
                    <i class="fas fa-users-cog mr-1"></i> Batch Assign
                </button>
            </div>

            <div class="relative w-64">
                <i class="fas fa-search absolute left-3 top-2.5 text-xs text-gray-400"></i>
                <input type="text" id="employeeSearchInput" onkeyup="filterEmployeeList()" placeholder="Search staff, code, dept..." class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-gray-300 rounded text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
            </div>
        </div>

        {{-- Modal Body: Scrollable --}}
        <div class="p-6 overflow-y-auto flex-1 space-y-4">
            
            {{-- TAB 1: Individual Table Mode --}}
            <div id="tabContentIndividual">
                <div class="overflow-x-auto border border-gray-200 rounded">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 uppercase text-[10px] tracking-wider font-semibold">
                            <tr>
                                <th class="py-2.5 px-3">Employee</th>
                                <th class="py-2.5 px-3">Department</th>
                                <th class="py-2.5 px-3">Current Role</th>
                                <th class="py-2.5 px-3">Assign New Role</th>
                                <th class="py-2.5 px-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($employees as $emp)
                            <tr class="hover:bg-gray-50/60 employee-row" data-search="{{ strtolower($emp->full_name . ' ' . $emp->employee_code . ' ' . ($emp->department ? $emp->department->name : '') . ' ' . ($emp->role ? $emp->role->name : '')) }}">
                                <td class="py-2.5 px-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full overflow-hidden bg-[#2D2D2D] text-white flex items-center justify-center text-[10px] font-bold shrink-0 border border-gray-200">
                                            @if($emp->avatar_url)
                                                <img src="{{ $emp->avatar_url }}" alt="{{ $emp->full_name }}" class="w-full h-full object-cover">
                                            @else
                                                <span>{{ $emp->initials }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="font-semibold text-[#2D2D2D] block">{{ $emp->full_name }}</span>
                                            <span class="text-[10px] text-gray-500 font-mono">{{ $emp->employee_code }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2.5 px-3 text-gray-600">
                                    {{ $emp->department ? $emp->department->name : 'Unassigned' }}
                                </td>
                                <td class="py-2.5 px-3">
                                    <span id="currentRoleBadge_{{ $emp->id }}" class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $emp->role ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $emp->role ? $emp->role->name : 'Unassigned' }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3">
                                    <select id="selectRole_{{ $emp->id }}" class="w-full px-2.5 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:border-[#2D2D2D] bg-white">
                                        <option value="">-- Unassigned --</option>
                                        @foreach($roles->where('slug', '!=', 'super-admin') as $r)
                                        <option value="{{ $r->id }}" {{ $emp->role_id == $r->id ? 'selected' : '' }}>
                                            {{ $r->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="py-2.5 px-3 text-right">
                                    <button type="button" onclick="saveSingleRole({{ $emp->id }})" id="btnSaveSingle_{{ $emp->id }}" class="px-2.5 py-1 bg-[#2D2D2D] text-white rounded text-[11px] font-semibold hover:bg-black transition-colors">
                                        Update
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-400">
                                    No company employees found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TAB 2: Batch Assign Mode --}}
            <div id="tabContentBatch" style="display: none;" class="space-y-4">
                <form id="batchAssignForm" onsubmit="submitBatchAssign(event)">
                    @csrf
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex-1">
                            <label class="block font-semibold text-xs text-[#2D2D2D] mb-1">Target Role for Selected Employees *</label>
                            <select id="batchTargetRoleId" name="role_id" required class="w-full sm:w-80 px-3 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:border-[#2D2D2D] bg-white">
                                <option value="">-- Choose Role to Assign --</option>
                                @foreach($roles->where('slug', '!=', 'super-admin') as $r)
                                <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->permissions_count }} permissions)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pt-2 sm:pt-4">
                            <button type="submit" id="btnSubmitBatch" class="px-4 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-black flex items-center gap-1.5">
                                <i class="fas fa-check-circle"></i> Apply Role to Selected
                            </button>
                        </div>
                    </div>

                    <div class="mt-3 flex items-center justify-between text-xs text-gray-600 px-1">
                        <label class="inline-flex items-center gap-2 cursor-pointer font-semibold">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" class="rounded border-gray-300 text-[#2D2D2D] focus:ring-0">
                            Select All Employees
                        </label>
                        <span id="selectedCountText" class="text-gray-500 font-medium">0 employees selected</span>
                    </div>

                    <div class="mt-2 border border-gray-200 rounded max-h-80 overflow-y-auto divide-y divide-gray-100">
                        @foreach($employees as $emp)
                        <label class="p-3 hover:bg-gray-50 flex items-center justify-between cursor-pointer employee-row" data-search="{{ strtolower($emp->full_name . ' ' . $emp->employee_code . ' ' . ($emp->department ? $emp->department->name : '') . ' ' . ($emp->role ? $emp->role->name : '')) }}">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}" class="batch-emp-checkbox rounded border-gray-300 text-[#2D2D2D] focus:ring-0" onchange="updateSelectedCount()">
                                <div>
                                    <span class="font-semibold text-xs text-[#2D2D2D] block">{{ $emp->full_name }}</span>
                                    <span class="text-[11px] text-gray-500 font-mono">{{ $emp->employee_code }} • {{ $emp->department ? $emp->department->name : 'No Dept' }}</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $emp->role ? 'bg-gray-100 text-gray-700' : 'bg-gray-50 text-gray-400' }}">
                                {{ $emp->role ? $emp->role->name : 'Unassigned' }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </form>
            </div>

        </div>

        {{-- Modal Footer --}}
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between shrink-0">
            <span class="text-[11px] text-gray-500">
                <i class="fas fa-shield-alt text-blue-600 mr-1"></i> Changes take effect immediately and synchronize user permissions.
            </span>
            <button type="button" onclick="closeAssignModal()" class="px-4 py-1.5 border border-gray-300 rounded text-xs font-semibold text-gray-700 hover:bg-white">
                Done
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openAssignModal(preselectedRoleId = null) {
        document.getElementById('companyRoleAssignModal').style.display = 'flex';
        switchAssignTab('individual');
        const searchInput = document.getElementById('employeeSearchInput');
        if (searchInput) {
            searchInput.value = '';
            filterEmployeeList();
        }
        if (preselectedRoleId) {
            const targetSelect = document.getElementById('batchTargetRoleId');
            if (targetSelect) targetSelect.value = preselectedRoleId;
        }
    }

    function closeAssignModal() {
        document.getElementById('companyRoleAssignModal').style.display = 'none';
    }

    function switchAssignTab(tab) {
        const indBtn = document.getElementById('tabBtnIndividual');
        const batchBtn = document.getElementById('tabBtnBatch');
        const indContent = document.getElementById('tabContentIndividual');
        const batchContent = document.getElementById('tabContentBatch');

        if (tab === 'individual') {
            indBtn.className = 'px-3 py-1.5 rounded text-xs font-semibold bg-[#2D2D2D] text-white';
            batchBtn.className = 'px-3 py-1.5 rounded text-xs font-semibold bg-white border border-gray-300 text-gray-700 hover:bg-gray-100';
            indContent.style.display = 'block';
            batchContent.style.display = 'none';
        } else {
            batchBtn.className = 'px-3 py-1.5 rounded text-xs font-semibold bg-[#2D2D2D] text-white';
            indBtn.className = 'px-3 py-1.5 rounded text-xs font-semibold bg-white border border-gray-300 text-gray-700 hover:bg-gray-100';
            indContent.style.display = 'none';
            batchContent.style.display = 'block';
        }
    }

    function filterEmployeeList() {
        const query = (document.getElementById('employeeSearchInput').value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.employee-row');
        rows.forEach(r => {
            const data = r.getAttribute('data-search') || '';
            r.style.display = data.includes(query) ? '' : 'none';
        });
    }

    async function saveSingleRole(empId) {
        const roleSelect = document.getElementById('selectRole_' + empId);
        const roleId = roleSelect ? roleSelect.value : null;
        const btn = document.getElementById('btnSaveSingle_' + empId);
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '...';

        try {
            const res = await fetch("{{ route('roles.assignEmployee') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    employee_id: empId,
                    role_id: roleId || null
                })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                const badge = document.getElementById('currentRoleBadge_' + empId);
                if (badge) {
                    badge.textContent = data.role_name;
                    if (data.role_id) {
                        badge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200';
                    } else {
                        badge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600';
                    }
                }
                if (window.Toast) {
                    Toast.fire({
                        icon: 'success',
                        title: data.message
                    });
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: data.message || 'Could not update employee role.'
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
            btn.textContent = originalText;
        }
    }

    function toggleSelectAll(masterCheckbox) {
        const checkboxes = document.querySelectorAll('.batch-emp-checkbox');
        checkboxes.forEach(cb => {
            const row = cb.closest('.employee-row');
            if (!row || row.style.display !== 'none') {
                cb.checked = masterCheckbox.checked;
            }
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const selected = document.querySelectorAll('.batch-emp-checkbox:checked').length;
        const text = document.getElementById('selectedCountText');
        if (text) {
            text.textContent = selected + ' employee' + (selected === 1 ? '' : 's') + ' selected';
        }
    }

    async function submitBatchAssign(e) {
        e.preventDefault();
        const roleId = document.getElementById('batchTargetRoleId').value;
        const checkedBoxes = Array.from(document.querySelectorAll('.batch-emp-checkbox:checked'));
        const empIds = checkedBoxes.map(cb => cb.value);

        if (!roleId) {
            Swal.fire({
                icon: 'warning',
                title: 'No Role Selected',
                text: 'Please choose a target role to assign.'
            });
            return;
        }

        if (empIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Employees Selected',
                text: 'Please select at least one employee using the checkboxes.'
            });
            return;
        }

        const btn = document.getElementById('btnSubmitBatch');
        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Applying...';

        try {
            const res = await fetch("{{ route('roles.bulkAssign') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    role_id: roleId,
                    employee_ids: empIds
                })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                closeAssignModal();
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
                    title: 'Batch Assignment Failed',
                    text: data.message || 'Could not assign roles in batch.'
                });
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Network error occurred while applying batch roles.'
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    }
</script>
@endpush
@endif
@endsection
