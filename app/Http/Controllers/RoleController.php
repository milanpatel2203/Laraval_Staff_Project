<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    private function authorizeRoleManage(): void
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission('roles.manage')) {
            abort(403, 'Unauthorized. You do not have permission to manage roles and permissions.');
        }
    }

    public function index()
    {
        $this->authorizeRoleManage();

        $roles = Role::withCount(['employees', 'permissions'])->with('permissions')->paginate(10)->withQueryString();
        $totalPermissions = Permission::count();
        
        // Exclude Super Admin profile from role assignment list (Super Admin is unique and fixed)
        $employees = Employee::with(['department', 'role'])
            ->where(function ($query) {
                $query->whereNull('role_id')
                      ->orWhereHas('role', function ($q) {
                          $q->where('slug', '!=', 'super-admin');
                      });
            })
            ->when(auth()->check(), function ($q) {
                $q->where('email', '!=', auth()->user()->email);
            })
            ->orderBy('first_name')
            ->get();

        return view('roles.index', compact('roles', 'totalPermissions', 'employees'));
    }

    public function create()
    {
        $this->authorizeRoleManage();

        $permissions = Permission::all()->groupBy('module');
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $this->authorizeRoleManage();

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        ActivityLog::record(
            "Role created: {$role->name}",
            "Assigned " . count($validated['permissions'] ?? []) . " permissions",
            'user-shield'
        );

        return redirect()->route('roles.index')->with('success', "Role '{$role->name}' created successfully.");
    }

    public function edit(Role $role)
    {
        $this->authorizeRoleManage();

        $permissions = Permission::all()->groupBy('module');
        $rolePermissionIds = $role->permissions->pluck('id')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissionIds'));
    }

    public function update(Request $request, Role $role)
    {
        $this->authorizeRoleManage();

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
            'slug' => $role->is_system ? $role->slug : Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        ActivityLog::record(
            "Role updated: {$role->name}",
            "Permissions updated (" . count($validated['permissions'] ?? []) . " assigned)",
            'user-shield'
        );

        return redirect()->route('roles.index')->with('success', "Role '{$role->name}' updated successfully.");
    }

    public function destroy(Role $role)
    {
        $this->authorizeRoleManage();

        if ($role->is_system) {
            return redirect()->back()->with('error', "System role '{$role->name}' cannot be deleted.");
        }

        if ($role->employees()->count() > 0) {
            return redirect()->back()->with('error', "Cannot delete role '{$role->name}' because it has assigned employees.");
        }

        $name = $role->name;
        $role->permissions()->detach();
        $role->delete();

        ActivityLog::record(
            "Role deleted: {$name}",
            "Removed from system access control",
            'user-times'
        );

        return redirect()->route('roles.index')->with('success', "Role '{$name}' deleted successfully.");
    }

    public function assignEmployeeRole(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized. Only Super Administrator can assign employee roles.'], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized. Only Super Administrator can assign employee roles.');
        }

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $employee = Employee::with('role')->findOrFail($validated['employee_id']);

        // Protect Super Admin profile: cannot change own/super admin role
        if (($employee->role && $employee->role->slug === 'super-admin') || (auth()->check() && $employee->email === auth()->user()->email)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Super Administrator profile is unique and cannot be modified.'], 422);
            }
            return redirect()->back()->with('error', 'Super Administrator profile is unique and cannot be modified.');
        }

        $role = !empty($validated['role_id']) ? Role::find($validated['role_id']) : null;

        // Disallow assigning super-admin role to other staff (super admin is only one)
        if ($role && $role->slug === 'super-admin') {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Super Administrator role cannot be assigned. There is only one Super Admin.'], 422);
            }
            return redirect()->back()->with('error', 'Super Administrator role cannot be assigned. There is only one Super Admin.');
        }

        $employee->role_id = $role ? $role->id : null;
        $employee->save();

        // Synchronize matching user account permissions
        $user = User::where('email', $employee->email)->first();
        if ($user) {
            $user->role_id = $role ? $role->id : null;
            if ($role) {
                $user->role_title = $role->name;
            }
            $user->save();
        }

        ActivityLog::record(
            "Role assigned: " . ($role ? $role->name : 'Unassigned'),
            "Assigned to employee {$employee->full_name} ({$employee->employee_code}) by " . auth()->user()->name,
            'user-shield'
        );

        $msg = "Role '" . ($role ? $role->name : 'Unassigned') . "' successfully assigned to {$employee->full_name}.";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'role_id' => $role ? $role->id : null,
                'role_name' => $role ? $role->name : 'Unassigned',
                'employee_id' => $employee->id,
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    public function bulkAssignEmployees(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized. Only Super Administrator can assign employee roles.'], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized. Only Super Administrator can assign employee roles.');
        }

        $validated = $request->validate([
            'role_id' => 'nullable|exists:roles,id',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        $role = !empty($validated['role_id']) ? Role::find($validated['role_id']) : null;
        if ($role && $role->slug === 'super-admin') {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Super Administrator role cannot be assigned in bulk. There is only one Super Admin.'], 422);
            }
            return redirect()->back()->with('error', 'Super Administrator role cannot be assigned in bulk.');
        }
        $roleId = $role ? $role->id : null;

        Employee::whereIn('id', $validated['employee_ids'])->update(['role_id' => $roleId]);

        // Sync corresponding users
        $employees = Employee::whereIn('id', $validated['employee_ids'])->get();
        foreach ($employees as $emp) {
            $user = User::where('email', $emp->email)->first();
            if ($user) {
                $user->role_id = $roleId;
                if ($role) {
                    $user->role_title = $role->name;
                }
                $user->save();
            }
        }

        $count = count($validated['employee_ids']);
        $roleTitle = $role ? $role->name : 'Unassigned';

        ActivityLog::record(
            "Bulk role assignment: {$roleTitle}",
            "Assigned to {$count} company employee(s) by " . auth()->user()->name,
            'user-shield'
        );

        $msg = "Role '{$roleTitle}' successfully assigned to {$count} company employee(s).";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'count' => $count,
                'role_name' => $roleTitle,
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }
}
