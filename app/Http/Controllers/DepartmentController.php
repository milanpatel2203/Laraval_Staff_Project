<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user->hasPermission('departments.view')) {
            abort(403, 'Unauthorized. You do not have permission to view departments.');
        }

        $canManageDepartments = $user->hasPermission('departments.manage');
        $departments = Department::withCount('employees')->orderBy('name')->paginate(10)->withQueryString();
        return view('departments.index', compact('departments', 'canManageDepartments'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermission('departments.manage')) {
            abort(403, 'Unauthorized. You do not have permission to manage departments.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:departments,code',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ]);

        Department::create($validated);

        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    public function update(Request $request, Department $department)
    {
        $user = auth()->user();
        if (!$user->hasPermission('departments.manage')) {
            abort(403, 'Unauthorized. You do not have permission to manage departments.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:departments,code,' . $department->id,
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ]);

        $department->update($validated);

        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $user = auth()->user();
        if (!$user->hasPermission('departments.manage')) {
            abort(403, 'Unauthorized. You do not have permission to delete departments.');
        }

        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }
}
