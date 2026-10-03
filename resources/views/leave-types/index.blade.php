@extends('layouts.app')

@section('title', 'Leave Types')
@section('page-title', 'Leave Types')

@section('content')
<div class="space-y-6">
    {{-- Alerts --}}
    @if(session('success'))
    <div class="bg-white border border-[#2D2D2D] text-[#2D2D2D] px-4 py-3 rounded flex items-center gap-2.5 text-sm font-medium">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-white border border-gray-400 text-[#2D2D2D] px-4 py-3 rounded flex items-center gap-2.5 text-sm font-medium">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">Leave Types ({{ $leaveTypes->count() }})</h2>
            <p class="text-xs text-gray-500 mt-0.5">Manage leave types and annual allocations.</p>
        </div>
        <button onclick="toggleAddModal()" class="bg-[#2D2D2D] hover:bg-[#1a1a1a] text-white text-xs font-semibold px-4 py-2 rounded inline-flex items-center gap-2">
            <i class="fas fa-plus"></i> Add Leave Type
        </button>
    </div>

    {{-- Leave Types Table --}}
    <div class="bg-white border border-gray-200 rounded overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/50 text-gray-500 uppercase tracking-wider">
                        <th class="py-3 px-4 font-semibold w-24">Code</th>
                        <th class="py-3 px-4 font-semibold">Leave Type</th>
                        <th class="py-3 px-4 font-semibold w-32">Annual Allocation</th>
                        <th class="py-3 px-4 font-semibold w-24">Type</th>
                        <th class="py-3 px-4 font-semibold w-24">Status</th>
                        <th class="py-3 px-4 font-semibold w-28 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-[13px]">
                    @forelse($leaveTypes as $leaveType)
                    <tr class="hover:bg-gray-50/50">
                        <td class="py-3 px-4 font-bold text-[#2D2D2D]">{{ $leaveType->code }}</td>
                        <td class="py-3 px-4 font-medium text-[#2D2D2D]">{{ $leaveType->name }}</td>
                        <td class="py-3 px-4 text-[#2D2D2D]">{{ $leaveType->annual_allocation }} days</td>
                        <td class="py-3 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $leaveType->is_paid ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                                {{ $leaveType->is_paid ? 'Paid' : 'Unpaid' }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold tracking-wider uppercase {{ $leaveType->status === 'active' ? 'bg-[#2D2D2D] text-white' : 'bg-gray-200 text-gray-700' }}">
                                {{ ucfirst($leaveType->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <button onclick="openEditModal({{ json_encode($leaveType) }})" class="w-7 h-7 rounded border border-gray-300 bg-white text-[#2D2D2D] hover:bg-[#2D2D2D] hover:text-white flex items-center justify-center text-xs" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('leave-types.destroy', $leaveType->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this leave type?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-7 h-7 rounded border border-gray-300 bg-white text-gray-500 hover:bg-[#2D2D2D] hover:text-white flex items-center justify-center text-xs" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-gray-400">No leave types found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Modal --}}
<div id="addModal" class="fixed inset-0 bg-[#2D2D2D]/60 flex items-center justify-center z-50 p-4" style="display: none;">
    <div class="bg-white rounded border border-[#2D2D2D] w-full max-w-md overflow-hidden shadow-lg">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
            <h3 class="text-sm font-bold text-[#2D2D2D]">Add New Leave Type</h3>
            <button onclick="toggleAddModal()" class="text-gray-400 hover:text-[#2D2D2D] text-lg font-bold">&times;</button>
        </div>
        <form action="{{ route('leave-types.store') }}" method="POST">
            @csrf
            <div class="p-5 space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Leave Type Name *</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="e.g. Casual Leave">
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Code *</label>
                    <input type="text" name="code" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="e.g. CL">
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Annual Allocation (Days) *</label>
                    <input type="number" name="annual_allocation" required min="0" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="e.g. 12">
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Type *</label>
                    <select name="is_paid" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        <option value="1">Paid</option>
                        <option value="0">Unpaid</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Status *</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex justify-end gap-2 text-xs">
                <button type="button" onclick="toggleAddModal()" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-100">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-[#2D2D2D] text-white rounded font-semibold hover:bg-[#1a1a1a]">Save Leave Type</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editModal" class="fixed inset-0 bg-[#2D2D2D]/60 flex items-center justify-center z-50 p-4" style="display: none;">
    <div class="bg-white rounded border border-[#2D2D2D] w-full max-w-md overflow-hidden shadow-lg">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
            <h3 class="text-sm font-bold text-[#2D2D2D]">Edit Leave Type</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-[#2D2D2D] text-lg font-bold">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="p-5 space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Leave Type Name *</label>
                    <input type="text" name="name" id="edit_name" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Code *</label>
                    <input type="text" name="code" id="edit_code" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Annual Allocation (Days) *</label>
                    <input type="number" name="annual_allocation" id="edit_annual_allocation" required min="0" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Type *</label>
                    <select name="is_paid" id="edit_is_paid" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        <option value="1">Paid</option>
                        <option value="0">Unpaid</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Status *</label>
                    <select name="status" id="edit_status" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex justify-end gap-2 text-xs">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-100">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-[#2D2D2D] text-white rounded font-semibold hover:bg-[#1a1a1a]">Update Leave Type</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleAddModal() {
    const modal = document.getElementById('addModal');
    modal.style.display = modal.style.display === 'none' ? 'flex' : 'none';
}

function openEditModal(leaveType) {
    document.getElementById('editForm').action = '/leave-types/' + leaveType.id;
    document.getElementById('edit_name').value = leaveType.name;
    document.getElementById('edit_code').value = leaveType.code;
    document.getElementById('edit_annual_allocation').value = leaveType.annual_allocation;
    document.getElementById('edit_is_paid').value = leaveType.is_paid ? '1' : '0';
    document.getElementById('edit_status').value = leaveType.status;
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>
@endpush
