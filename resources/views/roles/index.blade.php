@extends('layouts.app')

@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')

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

    @if(session('error'))
    <div class="bg-white border border-gray-400 text-[#2D2D2D] px-4 py-3 rounded flex items-center justify-between text-sm font-medium">
        <div class="flex items-center gap-2.5">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-xs text-gray-500 hover:text-black">&times;</button>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">Access Control Roles ({{ $roles->count() }})</h2>
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
                        <a href="{{ route('roles.edit', $role->id) }}" class="w-7 h-7 rounded border border-gray-300 bg-white text-[#2D2D2D] hover:bg-[#2D2D2D] hover:text-white flex items-center justify-center text-xs" title="Edit Role & Permissions">
                            <i class="fas fa-edit"></i>
                        </a>
                        @if(!$role->is_system)
                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete role {{ $role->name }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-7 h-7 rounded border border-gray-300 bg-white text-gray-500 hover:bg-[#2D2D2D] hover:text-white flex items-center justify-center text-xs" title="Delete Role">
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
</div>
@endsection
