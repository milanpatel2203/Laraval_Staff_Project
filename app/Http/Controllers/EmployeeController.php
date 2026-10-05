<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
<<<<<<< HEAD
use App\Models\Team;
=======
use App\Models\User;
use App\Services\CsvExportService;
>>>>>>> 58d9b53 (declare all imports at top with use statements and remove inline namespaces)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermission('employees.view')) {
            abort(403, 'Unauthorized. You do not have permission to view employees.');
        }

        $query = Employee::with(['department', 'role']);

        // Role-based data scoping
        if (!$user->isSuperAdmin() && !$user->hasPermission('settings.manage') && !$user->isHRManager()) {
            if ($user->isDepartmentManager() && $user->linked_employee?->department_id) {
                $query->where('department_id', $user->linked_employee->department_id);
            } elseif ($user->isStaff() && $user->linked_employee) {
                $query->where('id', $user->linked_employee->id);
            }
        }

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
<<<<<<< HEAD
        $teams = Team::where('status', 'active')->orderBy('name')->get();
=======
        $roles = Role::orderBy('name')->get();
>>>>>>> 58d9b53 (declare all imports at top with use statements and remove inline namespaces)

        return view('employees.index', compact('employees', 'departments', 'teams'));
    }

    public function create()
    {
        $user = auth()->user();
        if (!$user->hasPermission('employees.create')) {
            abort(403, 'Unauthorized. You do not have permission to create employees.');
        }

        $departments = Department::where('status', 'active')->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
<<<<<<< HEAD
        $teams = Team::where('status', 'active')->orderBy('name')->get();

=======
        
>>>>>>> 58d9b53 (declare all imports at top with use statements and remove inline namespaces)
        // Auto-generate employee code
        $lastEmp = Employee::latest('id')->first();
        $nextNumber = $lastEmp ? ((int) str_replace('EMP-', '', $lastEmp->employee_code) + 1) : 1;
        $suggestedCode = 'EMP-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return view('employees.create', compact('departments', 'roles', 'teams', 'suggestedCode'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermission('employees.create')) {
            abort(403, 'Unauthorized. You do not have permission to create employees.');
        }

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
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if (!auth()->user()->isSuperAdmin()) {
            unset($validated['role_id']);
        }

        $employee = Employee::create($validated);

        if (!empty($validated['avatar'])) {
            User::where('email', $employee->email)->update(['avatar' => $validated['avatar']]);
        }

        if (auth()->user()->isSuperAdmin() && !empty($validated['role_id'])) {
            $user = User::where('email', $employee->email)->first();
            if ($user) {
                $role = Role::find($validated['role_id']);
                $user->role_id = $validated['role_id'];
                if ($role) {
                    $user->role_title = $role->name;
                }
                $user->save();
            }
        }

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit(Employee $employee)
    {
        $user = auth()->user();
        if (!$user->hasPermission('employees.edit')) {
            abort(403, 'Unauthorized. You do not have permission to edit employees.');
        }

        $departments = Department::where('status', 'active')->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
<<<<<<< HEAD
        $teams = Team::where('status', 'active')->orderBy('name')->get();

        return view('employees.edit', compact('employee', 'departments', 'roles', 'teams'));
=======
        return view('employees.edit', compact('employee', 'departments', 'roles'));
>>>>>>> 58d9b53 (declare all imports at top with use statements and remove inline namespaces)
    }

    public function update(Request $request, Employee $employee)
    {
        $user = auth()->user();
        if (!$user->hasPermission('employees.edit')) {
            abort(403, 'Unauthorized. You do not have permission to edit employees.');
        }

        $validated = $request->validate([
            'employee_code' => 'required|string|max:50|unique:employees,employee_code,' . $employee->id,
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'department_id' => 'nullable|exists:departments,id',
            'team_id' => 'nullable|exists:teams,id',
            'role_id' => 'nullable|exists:roles,id',
            'designation' => 'required|string|max:100',
            'joining_date' => 'required|date',
            'salary' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive,terminated',
            'address' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'remove_avatar' => 'nullable|boolean',
        ]);

        if ($request->boolean('remove_avatar') && $employee->avatar) {
            Storage::disk('public')->delete($employee->avatar);
            $validated['avatar'] = null;
        } elseif ($request->hasFile('avatar')) {
            if ($employee->avatar) {
                Storage::disk('public')->delete($employee->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        // Bidirectional synchronization to User model
        $targetAvatar = array_key_exists('avatar', $validated) ? $validated['avatar'] : $employee->avatar;
        $linkedUser = $employee->linked_user;
        if ($linkedUser) {
            $userUpdate = [];
            if (array_key_exists('avatar', $validated)) {
                $userUpdate['avatar'] = $targetAvatar;
            }
            if (!empty($validated['phone'])) {
                $userUpdate['phone'] = $validated['phone'];
                $userUpdate['mobile'] = $validated['phone'];
            }
            if (!empty($validated['first_name'])) {
                $userUpdate['name'] = trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? ''));
            }
            if (!empty($userUpdate)) {
                $linkedUser->update($userUpdate);
            }
        }

        // Direct email user
        User::where('email', $employee->email)->update(
            array_filter([
                'avatar' => array_key_exists('avatar', $validated) ? $targetAvatar : null,
                'phone' => $validated['phone'] ?? null,
                'mobile' => $validated['phone'] ?? null,
                'name' => isset($validated['first_name']) ? trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? '')) : null,
            ], fn($v) => !is_null($v))
        );

        // Sync alias user accounts
        $aliasUserData = array_filter([
            'avatar' => array_key_exists('avatar', $validated) ? $targetAvatar : null,
            'phone' => $validated['phone'] ?? null,
            'mobile' => $validated['phone'] ?? null,
            'name' => isset($validated['first_name']) ? trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? '')) : null,
        ], fn($v) => !is_null($v));

        if (!empty($aliasUserData)) {
            $linkedUser = $employee->linked_user;
            if ($linkedUser) {
                $linkedUser->update($aliasUserData);
            }
        }

        if (!auth()->user()->isSuperAdmin()) {
            unset($validated['role_id']);
        } else if (array_key_exists('role_id', $validated)) {
            $user = User::where('email', $employee->email)->first();
            if ($user) {
                $user->role_id = $validated['role_id'];
                if ($validated['role_id']) {
                    $role = Role::find($validated['role_id']);
                    if ($role) {
                        $user->role_title = $role->name;
                    }
                }
                $user->save();
            }
        }

        $employee->update($validated);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $user = auth()->user();
        if (!$user->hasPermission('employees.delete')) {
            abort(403, 'Unauthorized. You do not have permission to delete employees.');
        }

        if ($employee->avatar) {
            Storage::disk('public')->delete($employee->avatar);
        }

        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermission('employees.view')) {
            abort(403, 'Unauthorized. You do not have permission to view employees.');
        }

        $query = Employee::with(['department', 'role']);

        // Role-based data scoping
        if (!$user->isSuperAdmin() && !$user->hasPermission('settings.manage') && !$user->isHRManager()) {
            if ($user->isDepartmentManager() && $user->linked_employee?->department_id) {
                $query->where('department_id', $user->linked_employee->department_id);
            } elseif ($user->isStaff() && $user->linked_employee) {
                $query->where('id', $user->linked_employee->id);
            }
        }

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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->get('filter') === 'new_hires') {
            $query->where('joining_date', '>=', now()->subDays(30));
        }

        $employees = $query->orderBy('first_name')->get();

        $headers = [
            'Employee Code',
            'Full Name',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Department',
            'Designation',
            'Role',
            'Status',
            'Joining Date',
            'Salary',
            'Address',
        ];

        $rows = [];
        foreach ($employees as $emp) {
            $rows[] = [
                $emp->employee_code,
                $emp->full_name,
                $emp->first_name,
                $emp->last_name,
                $emp->email,
                $emp->phone ?? '',
                $emp->department ? $emp->department->name : 'N/A',
                $emp->designation,
                $emp->role ? $emp->role->name : 'N/A',
                ucfirst($emp->status),
                $emp->joining_date ? $emp->joining_date->format('Y-m-d') : '',
                $emp->salary,
                $emp->address ?? '',
            ];
        }

        $filename = 'employees_export_' . now()->format('Y_m_d_His') . '.csv';

        return CsvExportService::streamDownload($filename, $headers, $rows);
    }
}
