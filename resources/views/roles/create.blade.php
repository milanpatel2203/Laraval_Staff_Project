@extends('layouts.app')

@section('title', 'Create Role')
@section('page-title', 'Create Role')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">Create New Access Role</h2>
            <p class="text-xs text-gray-500 mt-0.5">Define role details and authorize specific module permissions.</p>
        </div>
        <a href="{{ route('roles.index') }}" class="px-4 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100 flex items-center gap-1.5">
            <i class="fas fa-arrow-left"></i> Back to Roles
        </a>
    </div>

    @if($errors->any())
    <div class="bg-white border border-gray-400 text-[#2D2D2D] px-4 py-3 rounded flex items-center gap-2.5 text-sm font-medium">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    <form action="{{ route('roles.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Role Basics --}}
        <div class="bg-white border border-gray-200 rounded p-6">
            <h3 class="text-sm font-bold text-[#2D2D2D] border-b border-gray-200 pb-3 mb-4">Role Information</h3>
            <div class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Role Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Finance Auditor" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Responsibilities and scope for users with this role..." class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Permissions Grid by Module --}}
        <div class="bg-white border border-gray-200 rounded p-6">
            <div class="flex items-center justify-between border-b border-gray-200 pb-3 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-[#2D2D2D]">Module Permissions</h3>
                    <p class="text-xs text-gray-500">Check the permissions granted to this role.</p>
                </div>
                <button type="button" onclick="toggleAllGlobal()" class="text-xs font-semibold text-[#2D2D2D] underline">
                    Toggle All
                </button>
            </div>

            <div class="space-y-6">
                @foreach($permissions as $moduleName => $perms)
                <div class="border border-gray-200 rounded p-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-xs uppercase tracking-wide text-[#2D2D2D]">{{ $moduleName }}</span>
                            <span class="text-[10px] text-gray-400">({{ $perms->count() }} permissions)</span>
                        </div>
                        <button type="button" onclick="toggleModule('{{ Str::slug($moduleName) }}')" class="text-[11px] text-gray-500 hover:text-[#2D2D2D]">
                            Select / Deselect
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        @foreach($perms as $p)
                        <label class="flex items-start gap-2.5 p-2 rounded hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="permissions[]" value="{{ $p->id }}" class="perm-checkbox perm-{{ Str::slug($moduleName) }} mt-0.5 rounded border-gray-300 text-[#2D2D2D] focus:ring-0">
                            <div>
                                <span class="font-semibold text-[#2D2D2D] block">{{ $p->name }}</span>
                                <span class="text-[11px] text-gray-500">{{ $p->description }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('roles.index') }}" class="px-4 py-2 border border-gray-300 rounded font-medium text-xs text-gray-700 bg-white hover:bg-gray-100">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-[#2D2D2D] text-white rounded font-semibold text-xs hover:bg-[#1a1a1a] flex items-center gap-1.5">
                <i class="fas fa-save"></i> Save Role
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function toggleModule(moduleSlug) {
    const checkboxes = document.querySelectorAll('.perm-' + moduleSlug);
    const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
    checkboxes.forEach(cb => cb.checked = anyUnchecked);
}

function toggleAllGlobal() {
    const checkboxes = document.querySelectorAll('.perm-checkbox');
    const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
    checkboxes.forEach(cb => cb.checked = anyUnchecked);
}
</script>
@endpush
