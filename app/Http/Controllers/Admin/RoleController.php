<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index(Request $request)
    {
        $query = Role::withCount('users');
        
        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('display_name', 'like', "%{$request->search}%");
            });
        }
        
        // Filter by status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $roles = $query->byLevel()->paginate(20);

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $permissions = Permission::groupedByModule()->get()->groupBy('module');
        
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:roles', 'alpha_dash'],
            'display_name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'level' => ['required', 'integer', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ], [
            'name.required' => 'Nama role wajib diisi.',
            'name.unique' => 'Nama role sudah digunakan.',
            'name.alpha_dash' => 'Nama role hanya boleh berisi huruf, angka, dash, dan underscore.',
            'display_name.required' => 'Display name wajib diisi.',
            'level.required' => 'Level wajib diisi.',
        ]);

        $permissions = $validated['permissions'] ?? [];
        unset($validated['permissions']);

        $role = Role::create($validated);
        $role->permissions()->sync($permissions);

        AuditLog::log(
            'created',
            AuditLog::MODULE_ADMIN,
            'Role',
            $role->id,
            $role->display_name,
            null,
            $validated,
            'Role baru ditambahkan.'
        );

        return redirect()->route('admin.roles.index')
            ->with('success', "Role {$role->display_name} berhasil ditambahkan.");
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        $role->load(['permissions', 'users' => function ($q) {
            $q->active()->orderBy('name')->limit(20);
        }]);

        $permissionsByModule = $role->permissions->groupBy('module');

        return view('admin.roles.show', compact('role', 'permissionsByModule'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        $permissions = Permission::groupedByModule()->get()->groupBy('module');
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('roles')->ignore($role->id), 'alpha_dash'],
            'display_name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'level' => ['required', 'integer', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ], [
            'name.required' => 'Nama role wajib diisi.',
            'name.unique' => 'Nama role sudah digunakan.',
            'display_name.required' => 'Display name wajib diisi.',
            'level.required' => 'Level wajib diisi.',
        ]);

        $permissions = $validated['permissions'] ?? [];
        unset($validated['permissions']);

        $oldValues = $role->only(['name', 'display_name', 'description', 'level', 'is_active']);
        $oldPermissions = $role->permissions->pluck('id')->toArray();

        $role->update($validated);
        $role->permissions()->sync($permissions);

        // Clear permission cache for all users with this role
        $role->users->each->clearPermissionCache();

        AuditLog::log(
            'updated',
            AuditLog::MODULE_ADMIN,
            'Role',
            $role->id,
            $role->display_name,
            array_merge($oldValues, ['permissions' => $oldPermissions]),
            array_merge($validated, ['permissions' => $permissions]),
            'Role diperbarui.'
        );

        return redirect()->route('admin.roles.index')
            ->with('success', "Role {$role->display_name} berhasil diperbarui.");
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        // Prevent deletion of system roles
        if (in_array($role->name, ['super_admin', 'admin'])) {
            return back()->with('error', 'Role sistem tidak dapat dihapus.');
        }

        // Check if role has users
        if ($role->users()->exists()) {
            return back()->with('error', 'Role tidak dapat dihapus karena masih memiliki pengguna.');
        }

        $name = $role->display_name;
        
        AuditLog::log(
            'deleted',
            AuditLog::MODULE_ADMIN,
            'Role',
            $role->id,
            $name,
            $role->toArray(),
            null,
            'Role dihapus.'
        );

        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role {$name} berhasil dihapus.");
    }
}
