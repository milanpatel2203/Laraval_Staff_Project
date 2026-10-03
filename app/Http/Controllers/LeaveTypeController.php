<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $this->authorize('view', LeaveType::class);

        $leaveTypes = LeaveType::all();

        return view('leave-types.index', compact('leaveTypes'));
    }

    public function create()
    {
        $this->authorize('create', LeaveType::class);

        return view('leave-types.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', LeaveType::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:leave_types,code',
            'annual_allocation' => 'required|integer|min:0',
            'is_paid' => 'required|boolean',
            'status' => 'required|in:active,inactive',
        ]);

        LeaveType::create($validated);

        return redirect()->route('leave-types.index')->with('success', 'Leave type created successfully.');
    }

    public function edit(LeaveType $leaveType)
    {
        $this->authorize('update', $leaveType);

        return view('leave-types.edit', compact('leaveType'));
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $this->authorize('update', $leaveType);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:leave_types,code,'.$leaveType->id,
            'annual_allocation' => 'required|integer|min:0',
            'is_paid' => 'required|boolean',
            'status' => 'required|in:active,inactive',
        ]);

        $leaveType->update($validated);

        return redirect()->route('leave-types.index')->with('success', 'Leave type updated successfully.');
    }

    public function destroy(LeaveType $leaveType)
    {
        $this->authorize('delete', $leaveType);

        if ($leaveType->leaves()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete leave type with existing leave requests.');
        }

        $leaveType->delete();

        return redirect()->route('leave-types.index')->with('success', 'Leave type deleted successfully.');
    }
}
