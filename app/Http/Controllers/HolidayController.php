<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);
        $holidays = Holiday::whereYear('date', $year)->orderBy('date')->get();

        return view('holidays.index', compact('holidays', 'year'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'date' => 'required|date',
            'type' => 'required|in:National,Gazetted,Restricted,Optional',
            'description' => 'nullable|string|max:500',
        ]);

        $holiday = Holiday::create($validated);

        ActivityLog::record(
            "Holiday added: {$holiday->name}",
            "Scheduled on {$holiday->date->format('d M Y')} ({$holiday->type})",
            'calendar-plus'
        );

        return redirect()->route('holidays.index', ['year' => $holiday->date->year])
            ->with('success', "Holiday '{$holiday->name}' added successfully.");
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'date' => 'required|date',
            'type' => 'required|in:National,Gazetted,Restricted,Optional',
            'description' => 'nullable|string|max:500',
        ]);

        $holiday->update($validated);

        return redirect()->route('holidays.index', ['year' => $holiday->date->year])
            ->with('success', "Holiday '{$holiday->name}' updated successfully.");
    }

    public function destroy(Holiday $holiday)
    {
        $name = $holiday->name;
        $holiday->delete();

        ActivityLog::record(
            "Holiday removed: {$name}",
            "Removed from organizational calendar",
            'calendar-times'
        );

        return redirect()->back()->with('success', "Holiday '{$name}' deleted successfully.");
    }
}
