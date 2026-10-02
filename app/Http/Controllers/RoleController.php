<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount(['employees', 'permissions'])->with('permissions')->get();
        $totalPermissions = Permission::count();

        return view('roles.index', compact('roles', 'totalPermissions'));
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy('module');
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
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
        $permissions = Permission::all()->groupBy('module');
        $rolePermissionIds = $role->permissions->pluck('id')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissionIds'));
    }

    public function update(Request $request, Role $role)
    {
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
}
