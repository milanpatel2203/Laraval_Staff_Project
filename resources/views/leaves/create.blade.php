@extends('layouts.app')

@section('title', 'Apply for Leave')

@section('page-title', 'Apply for Leave')

@section('content')

<div class="max-w-2xl">

    @if ($errors->any())
        <div class="mb-4 bg-white border border-red-300 text-red-600 px-4 py-3 rounded text-sm">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-white border border-red-300 text-red-600 px-4 py-3 rounded text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded p-6">

        <div class="mb-6">
            <h2 class="text-lg font-bold text-[#2D2D2D]">
                Apply for Leave
            </h2>

            <p class="text-xs text-gray-500 mt-1">
                Submit your leave request for approval.
            </p>
        </div>

        <form action="{{ route('leaves.store') }}" method="POST">
            @csrf

            <div class="space-y-5">

                {{-- Leave Type --}}
                <div>
                    <label
                        for="leave_type_id"
                        class="block text-xs font-semibold text-gray-700 mb-1.5"
                    >
                        Leave Type
                    </label>

                    <select
                        name="leave_type_id"
                        id="leave_type_id"
                        required
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#2D2D2D]"
                    >
                        <option value="">Select Leave Type</option>

                        @foreach($leaveTypes as $leaveType)
                            <option
                                value="{{ $leaveType->id }}"
                                {{ old('leave_type_id') == $leaveType->id ? 'selected' : '' }}
                            >
                                {{ $leaveType->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                {{-- Dates --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label
                            for="from_date"
                            class="block text-xs font-semibold text-gray-700 mb-1.5"
                        >
                            From Date
                        </label>

                        <input
                            type="date"
                            name="from_date"
                            id="from_date"
                            value="{{ old('from_date') }}"
                            required
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#2D2D2D]"
                        >
                    </div>

                    <div>
                        <label
                            for="to_date"
                            class="block text-xs font-semibold text-gray-700 mb-1.5"
                        >
                            To Date
                        </label>

                        <input
                            type="date"
                            name="to_date"
                            id="to_date"
                            value="{{ old('to_date') }}"
                            required
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#2D2D2D]"
                        >
                    </div>

                </div>

                {{-- Reason --}}
                <div>
                    <label
                        for="reason"
                        class="block text-xs font-semibold text-gray-700 mb-1.5"
                    >
                        Reason
                    </label>

                    <textarea
                        name="reason"
                        id="reason"
                        rows="4"
                        required
                        placeholder="Enter reason for leave..."
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#2D2D2D]"
                    >{{ old('reason') }}</textarea>
                </div>

                {{-- Buttons --}}
                <div class="flex items-center gap-2 pt-2">

                    <a
                        href="{{ route('leaves.index') }}"
                        class="px-4 py-2 text-xs rounded border border-gray-300 text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="px-4 py-2 text-xs rounded bg-[#2D2D2D] text-white hover:bg-[#1a1a1a]"
                    >
                        <i class="fas fa-paper-plane mr-1"></i>
                        Submit Leave
                    </button>

                </div>

            </div>
        </form>

    </div>

</div>

@endsection

