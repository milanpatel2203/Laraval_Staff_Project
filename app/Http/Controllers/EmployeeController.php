<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Team;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'role', 'team']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->get('filter') === 'new_hires') {
            $query->where('joining_date', '>=', now()->subDays(30));
        }

        $employees = $query->orderBy('first_name')->paginate(10)->withQueryString();
        $departments = Department::where('status', 'active')->orderBy('name')->get();
        $teams = Team::where('status', 'active')->orderBy('name')->get();

        return view('employees.index', compact('employees', 'departments', 'teams'));
    }

    public function create()
    {
        $departments = Department::where('status', 'active')->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        $teams = Team::where('status', 'active')->orderBy('name')->get();

        // Auto-generate employee code
        $lastEmp = Employee::latest('id')->first();
        $nextNumber = $lastEmp ? ((int) str_replace('EMP-', '', $lastEmp->employee_code) + 1) : 1;
        $suggestedCode = 'EMP-'.str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return view('employees.create', compact('departments', 'roles', 'teams', 'suggestedCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_code' => 'required|string|max:50|unique:employees,employee_code',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'department_id' => 'nullable|exists:departments,id',
            'team_id' => 'nullable|exists:teams,id',
            'role_id' => 'nullable|exists:roles,id',
            'designation' => 'required|string|max:100',
            'joining_date' => 'required|date',
            'salary' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive,terminated',
            'address' => 'nullable|string|max:500',
        ]);

        Employee::create($validated);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit(Employee $employee)
    {
        $departments = Department::where('status', 'active')->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        $teams = Team::where('status', 'active')->orderBy('name')->get();

        return view('employees.edit', compact('employee', 'departments', 'roles', 'teams'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_code' => 'required|string|max:50|unique:employees,employee_code,'.$employee->id,
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:employees,email,'.$employee->id,
            'phone' => 'nullable|string|max:20',
            'department_id' => 'nullable|exists:departments,id',
            'team_id' => 'nullable|exists:teams,id',
            'role_id' => 'nullable|exists:roles,id',
            'designation' => 'required|string|max:100',
            'joining_date' => 'required|date',
            'salary' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive,terminated',
            'address' => 'nullable|string|max:500',
        ]);

        $employee->update($validated);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }
}
