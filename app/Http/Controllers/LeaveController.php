<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Leave;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    /**
     * List all leaves.
     */
    public function index(Request $request)
    {
        $query = Leave::with('employee');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaves = $query->latest()->paginate(10)->withQueryString();

        return view('leaves.index', compact('leaves'));
    }

    /**
     * Approve a pending leave request.
     */
    public function approve(Leave $leave)
    {
        $leave->update(['status' => 'approved']);

        ActivityLog::record(
            "Leave approved for {$leave->employee->full_name}",
            "{$leave->type} ({$leave->days} days) from {$leave->from_date->format('d M')} to {$leave->to_date->format('d M')}",
            'check-circle'
        );

        return redirect()->back()->with('success', "Leave approved for {$leave->employee->full_name}.");
    }

    /**
     * Reject a pending leave request.
     */
    public function reject(Leave $leave)
    {
        $leave->update(['status' => 'rejected']);

        ActivityLog::record(
            "Leave rejected for {$leave->employee->full_name}",
            "{$leave->type} ({$leave->days} days) was declined",
            'times-circle'
        );

        return redirect()->back()->with('success', "Leave rejected for {$leave->employee->full_name}.");
    }
}
