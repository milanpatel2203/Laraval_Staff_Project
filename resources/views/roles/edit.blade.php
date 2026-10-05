@extends('layouts.app')

@section('title', 'Edit Role')
@section('page-title', 'Edit Role')

@section('content')
<div class="max-w-6xl mx-auto space-y-3">
    <div class="flex items-center justify-between bg-white border border-gray-200 rounded px-4 py-2.5 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-[#2D2D2D]">Edit Role: {{ $role->name }}</h2>
            <p class="text-[11px] text-gray-500">Modify role details and authorized module permissions.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('roles.index') }}" class="px-3 py-1.5 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100 flex items-center gap-1.5">
                <i class="fas fa-arrow-left"></i> Cancel
            </a>
            <button type="submit" form="roleEditForm" class="px-3.5 py-1.5 bg-[#2D2D2D] text-white rounded font-semibold text-xs hover:bg-[#1a1a1a] flex items-center gap-1.5 shadow-sm">
                <i class="fas fa-save"></i> Update Role
            </button>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded flex items-center gap-2 text-xs font-medium">
        <i class="fas fa-exclamation-circle text-red-500"></i>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    <form id="roleEditForm" action="{{ route('roles.update', $role->id) }}" method="POST" class="space-y-3">
        @csrf
        @method('PUT')

        {{-- Role Basics (Compact horizontal card) --}}
        <div class="bg-white border border-gray-200 rounded p-3.5 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Role Name *</label>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}" required class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div class="md:col-span-2">
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Description</label>
                    <input type="text" name="description" value="{{ old('description', $role->description) }}" placeholder="Responsibilities and scope for users with this role..." class="w-full px-2.5 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
            </div>
        </div>

        {{-- Permissions Grid by Module --}}
        <div class="bg-white border border-gray-200 rounded p-3.5 shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-200 pb-2 mb-3">
                <div class="flex items-center gap-2">
                    <h3 class="text-xs font-bold text-[#2D2D2D] uppercase tracking-wide">Module Permissions</h3>
                    <span class="text-[11px] text-gray-500">Update granted permissions for this role</span>
                </div>
                <button type="button" onclick="toggleAllGlobal()" class="text-xs font-semibold text-[#2D2D2D] hover:underline flex items-center gap-1">
                    <i class="fas fa-check-double text-[10px]"></i> Toggle All Permissions
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5">
                @foreach($permissions as $moduleName => $perms)
                <div class="border border-gray-200 rounded-lg p-2.5 bg-gray-50/50 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between border-b border-gray-200 pb-1.5 mb-2">
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-[11px] uppercase tracking-wider text-[#2D2D2D]">{{ $moduleName }}</span>
                                <span class="text-[10px] text-gray-500 bg-white border border-gray-200 px-1.5 py-0.5 rounded-full font-medium">{{ $perms->count() }}</span>
                            </div>
                            <button type="button" onclick="toggleModule('{{ Str::slug($moduleName) }}')" class="text-[10px] font-medium text-blue-600 hover:text-blue-800">
                                Toggle
                            </button>
                        </div>

                        <div class="space-y-1.5">
                            @foreach($perms as $p)
                            <label class="flex items-start gap-2 p-1 rounded hover:bg-white cursor-pointer transition-colors">
                                <input type="checkbox" name="permissions[]" value="{{ $p->id }}" {{ in_array($p->id, old('permissions', $rolePermissionIds)) ? 'checked' : '' }} class="perm-checkbox perm-{{ Str::slug($moduleName) }} mt-0.5 rounded border-gray-300 text-[#2D2D2D] focus:ring-0">
                                <div class="min-w-0">
                                    <span class="font-semibold text-xs text-[#2D2D2D] block truncate leading-tight">{{ $p->name }}</span>
                                    <span class="text-[10px] text-gray-500 block leading-tight line-clamp-1" title="{{ $p->description }}">{{ $p->description }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-between bg-white border border-gray-200 rounded px-4 py-2.5 shadow-sm">
            <span class="text-[11px] text-gray-500">Ensure necessary permissions are checked before saving changes.</span>
            <div class="flex gap-2">
                <a href="{{ route('roles.index') }}" class="px-3.5 py-1.5 border border-gray-300 rounded font-medium text-xs text-gray-700 bg-white hover:bg-gray-100">Cancel</a>
                <button type="submit" class="px-4 py-1.5 bg-[#2D2D2D] text-white rounded font-semibold text-xs hover:bg-[#1a1a1a] flex items-center gap-1.5 shadow-sm">
                    <i class="fas fa-save"></i> Update Role
                </button>
            </div>
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
