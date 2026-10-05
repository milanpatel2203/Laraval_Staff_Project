@extends('layouts.app')

@section('title', 'Holiday Calendar')
@section('page-title', 'Holidays')

@section('content')
<div class="space-y-6">

    {{-- Header & Year Filter --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">Holiday Calendar — {{ $year }} ({{ $holidays->total() }})</h2>
            <p class="text-xs text-gray-500 mt-0.5">Manage declared public holidays, national breaks, and company observances.</p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('holidays.index') }}" method="GET" class="flex items-center gap-2">
                <select name="year" onchange="this.form.submit()" class="px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] bg-white">
                    @for($y = now()->year - 1; $y <= now()->year + 2; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
            @if($canManageHolidays)
            <button onclick="toggleAddModal()" class="px-4 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a] flex items-center gap-1.5">
                <i class="fas fa-plus"></i> Add Holiday
            </button>
            @endif
        </div>
    </div>

    {{-- Holiday List Table using Common Component --}}
    <x-table 
        :headers="[
            ['label' => 'Date', 'class' => 'w-24'],
            ['label' => 'Holiday Name'],
            ['label' => 'Day', 'class' => 'w-28'],
            ['label' => 'Classification', 'class' => 'w-28'],
            ['label' => 'Description'],
            ['label' => 'Actions', 'class' => 'w-24', 'align' => 'right']
        ]"
        :empty="$holidays->isEmpty()" 
        :pagination="$holidays"
        emptyMessage="No holidays scheduled for this year. Click 'Add Holiday' to schedule one.">
        @foreach($holidays as $h)
        <tr class="hover:bg-gray-50/50">
            <td class="py-3 px-4">
                <div class="w-12 h-12 rounded bg-[#2D2D2D] text-[#F5F5F5] flex flex-col items-center justify-center shrink-0">
                    <span class="text-base font-bold leading-tight">{{ $h->date->format('d') }}</span>
                    <span class="text-[10px] uppercase font-semibold">{{ $h->date->format('M') }}</span>
                </div>
            </td>
            <td class="py-3 px-4 font-semibold text-[#2D2D2D]">{{ $h->name }}</td>
            <td class="py-3 px-4 text-gray-600">{{ $h->date->format('l') }}</td>
            <td class="py-3 px-4">
                <x-badge :variant="$h->type === 'National' ? 'active' : 'inactive'">
                    {{ $h->type }}
                </x-badge>
            </td>
            <td class="py-3 px-4 text-gray-500">{{ $h->description ?: '—' }}</td>
            <td class="py-3 px-4 text-right">
                @if($canManageHolidays)
                <div class="inline-flex items-center gap-1.5">
                    <button onclick="openEditModal({{ json_encode($h) }})" class="btn-action-edit text-xs" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form action="{{ route('holidays.destroy', $h->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this holiday?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-action-delete text-xs" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
                @else
                <span class="text-xs text-gray-400">—</span>
                @endif
            </td>
        </tr>
        @endforeach
    </x-table>
</div>

{{-- Add Holiday Modal --}}
<div id="addModal" class="fixed inset-0 bg-[#2D2D2D]/60 flex items-center justify-center z-50 p-4" style="display: none;">
    <div class="bg-white rounded border border-[#2D2D2D] w-full max-w-md overflow-hidden shadow-lg">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
            <h3 class="text-sm font-bold text-[#2D2D2D]">Add New Holiday</h3>
            <button onclick="toggleAddModal()" class="text-gray-400 hover:text-[#2D2D2D] text-lg font-bold">&times;</button>
        </div>
        <form action="{{ route('holidays.store') }}" method="POST">
            @csrf
            <div class="p-5 space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Holiday Name *</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="e.g. Independence Day">
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Date *</label>
                    <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Classification *</label>
                    <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        <option value="Gazetted">Gazetted Holiday</option>
                        <option value="National">National Holiday</option>
                        <option value="Restricted">Restricted Holiday</option>
                        <option value="Optional">Optional Holiday</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="Optional notes or details..."></textarea>
                </div>
            </div>
            <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex justify-end gap-2 text-xs">
                <button type="button" onclick="toggleAddModal()" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-100">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-[#2D2D2D] text-white rounded font-semibold hover:bg-[#1a1a1a]">Save Holiday</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Holiday Modal --}}
<div id="editModal" class="fixed inset-0 bg-[#2D2D2D]/60 flex items-center justify-center z-50 p-4" style="display: none;">
    <div class="bg-white rounded border border-[#2D2D2D] w-full max-w-md overflow-hidden shadow-lg">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
            <h3 class="text-sm font-bold text-[#2D2D2D]">Edit Holiday</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-[#2D2D2D] text-lg font-bold">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="p-5 space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Holiday Name *</label>
                    <input type="text" name="name" id="edit_name" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Date *</label>
                    <input type="date" name="date" id="edit_date" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Classification *</label>
                    <select name="type" id="edit_type" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        <option value="Gazetted">Gazetted Holiday</option>
                        <option value="National">National Holiday</option>
                        <option value="Restricted">Restricted Holiday</option>
                        <option value="Optional">Optional Holiday</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Description</label>
                    <textarea name="description" id="edit_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]"></textarea>
                </div>
            </div>
            <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex justify-end gap-2 text-xs">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-100">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-[#2D2D2D] text-white rounded font-semibold hover:bg-[#1a1a1a]">Update Holiday</button>
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

function openEditModal(holiday) {
    document.getElementById('editForm').action = '/holidays/' + holiday.id;
    document.getElementById('edit_name').value = holiday.name;
    document.getElementById('edit_date').value = holiday.date.split('T')[0];
    document.getElementById('edit_type').value = holiday.type;
    document.getElementById('edit_description').value = holiday.description || '';
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>
@endpush
