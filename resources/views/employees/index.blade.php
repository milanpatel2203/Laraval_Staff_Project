@extends('layouts.app')

@section('title', 'Employees')
@section('page-title', 'Employees')

@section('content')
<div class="space-y-6">
    {{-- Alerts --}}
    @if(session('success'))
    <div class="bg-white border border-[#2D2D2D] text-[#2D2D2D] px-4 py-3 rounded flex items-center gap-2.5 text-sm font-medium">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">Employees Directory ({{ $employees->total() }})</h2>
            <p class="text-xs text-gray-500 mt-0.5">Search, filter, and manage staff records.</p>
        </div>
        <a href="{{ route('employees.create') }}" class="bg-[#2D2D2D] hover:bg-[#1a1a1a] text-white text-xs font-semibold px-4 py-2 rounded inline-flex items-center gap-2">
            <i class="fas fa-user-plus"></i> Add Employee
        </a>
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

    {{-- Employees Table --}}
    <div class="bg-white border border-gray-200 rounded overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/50 text-gray-500 uppercase tracking-wider">
                        <th class="py-3 px-4 font-semibold w-24">Code</th>
                        <th class="py-3 px-4 font-semibold">Employee</th>
                        <th class="py-3 px-4 font-semibold">Contact</th>
                        <th class="py-3 px-4 font-semibold">Department</th>
                        <th class="py-3 px-4 font-semibold">Designation</th>
                        <th class="py-3 px-4 font-semibold">Joined</th>
                        <th class="py-3 px-4 font-semibold">Salary</th>
                        <th class="py-3 px-4 font-semibold w-24">Status</th>
                        <th class="py-3 px-4 font-semibold w-24 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-[13px]">
                    @forelse($employees as $emp)
                    <tr class="hover:bg-gray-50/50">
                        <td class="py-3 px-4 font-bold text-[#2D2D2D]">{{ $emp->employee_code }}</td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-[#2D2D2D] text-[#F5F5F5] flex items-center justify-center text-xs shrink-0">
                                    <i class="fas fa-user"></i>
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
                        <td class="py-3 px-4">
                            <span class="font-medium text-[#2D2D2D] block">{{ $emp->designation }}</span>
                            @if($emp->role)
                            <span class="inline-block mt-0.5 px-1.5 py-0.2 bg-gray-100 rounded text-[10px] text-gray-600 font-semibold">{{ $emp->role->name }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-gray-600">{{ $emp->joining_date ? $emp->joining_date->format('d M Y') : '—' }}</td>
                        <td class="py-3 px-4 font-medium text-[#2D2D2D]">₹{{ number_format($emp->salary, 2) }}</td>
                        <td class="py-3 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold tracking-wider uppercase {{ $emp->status === 'active' ? 'bg-[#2D2D2D] text-white' : 'bg-gray-200 text-gray-700' }}">
                                {{ ucfirst($emp->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('employees.edit', $emp->id) }}" class="w-7 h-7 rounded border border-gray-300 bg-white text-[#2D2D2D] hover:bg-[#2D2D2D] hover:text-white flex items-center justify-center text-xs" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('employees.destroy', $emp->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this employee record?');">
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
                        <td colspan="9" class="py-8 text-center text-gray-400">No employees found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
        <div class="p-4 border-t border-gray-200">
            {{ $employees->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
