@extends('layouts.app')

@section('title', 'Holiday Calendar')
@section('page-title', 'Holidays')

@section('content')
<div class="space-y-6">

    @if(session('success'))
    <div class="bg-white border border-[#2D2D2D] text-[#2D2D2D] px-4 py-3 rounded flex items-center justify-between text-sm font-medium">
        <div class="flex items-center gap-2.5">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-xs text-gray-500 hover:text-black">&times;</button>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-white border border-gray-400 text-[#2D2D2D] px-4 py-3 rounded flex items-center gap-2.5 text-sm font-medium">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    {{-- Header & Year Filter --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">Holiday Calendar — {{ $year }} ({{ $holidays->count() }})</h2>
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
            <button onclick="toggleAddModal()" class="px-4 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a] flex items-center gap-1.5">
                <i class="fas fa-plus"></i> Add Holiday
            </button>
        </div>
    </div>

    {{-- Holiday List Cards / Table --}}
    <div class="bg-white border border-gray-200 rounded overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/50 text-gray-500 uppercase tracking-wider">
                        <th class="py-3 px-4 font-semibold w-24">Date</th>
                        <th class="py-3 px-4 font-semibold">Holiday Name</th>
                        <th class="py-3 px-4 font-semibold w-28">Day</th>
                        <th class="py-3 px-4 font-semibold w-28">Classification</th>
                        <th class="py-3 px-4 font-semibold">Description</th>
                        <th class="py-3 px-4 font-semibold w-24 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-[13px]">
                    @forelse($holidays as $h)
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
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold tracking-wider uppercase 
                                {{ $h->type === 'National' ? 'bg-[#2D2D2D] text-white' : 'bg-gray-200 text-gray-800' }}">
                                {{ $h->type }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-gray-500">{{ $h->description ?: '—' }}</td>
                        <td class="py-3 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <button onclick="openEditModal({{ json_encode($h) }})" class="w-7 h-7 rounded border border-gray-300 bg-white text-[#2D2D2D] hover:bg-[#2D2D2D] hover:text-white flex items-center justify-center text-xs" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('holidays.destroy', $h->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this holiday?');">
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
                        <td colspan="6" class="py-8 text-center text-gray-400">
                            No holidays scheduled for year {{ $year }}. Click <strong>"Add Holiday"</strong> to schedule one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
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
