<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Position;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with(['role', 'unit', 'position']);
        
        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        
        // Filter by role
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }
        
        // Filter by unit
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        // Filter by status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $users = $query->orderBy('name')->paginate(20);
        $roles = Role::active()->byLevel()->get();
        $units = Unit::active()->sorted()->get();

        return view('admin.users.index', compact('users', 'roles', 'units'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::active()->byLevel()->get();
        $units = Unit::with('directorate')->active()->sorted()->get();
        $positions = Position::active()->byLevel()->get();
        
        return view('admin.users.create', compact('roles', 'units', 'positions'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:users', 'alpha_dash'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users'],
            'employee_id' => ['nullable', 'string', 'max:30', 'unique:users'],
            'role_id' => ['required', 'exists:roles,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, dash, dan underscore.',
            'name.required' => 'Nama wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'employee_id.unique' => 'NIP sudah digunakan.',
            'role_id.required' => 'Role wajib dipilih.',
        ]);

        // Generate random password
        $password = Str::random(12);
        $validated['password'] = Hash::make($password);
        $validated['must_change_password'] = true;

        $user = User::create($validated);

        AuditLog::log(
            'created',
            AuditLog::MODULE_USERS,
            'User',
            $user->id,
            $user->username,
            null,
            array_merge($validated, ['password' => '[HIDDEN]']),
            'Pengguna baru ditambahkan.'
        );

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna {$user->name} berhasil ditambahkan. Password sementara: {$password}");
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['role.permissions', 'unit.directorate', 'position']);
        
        $recentLogs = AuditLog::where('user_id', $user->id)
            ->recent()
            ->limit(10)
            ->get();

        return view('admin.users.show', compact('user', 'recentLogs'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $roles = Role::active()->byLevel()->get();
        $units = Unit::with('directorate')->active()->sorted()->get();
        $positions = Position::active()->byLevel()->get();
        
        return view('admin.users.edit', compact('user', 'roles', 'units', 'positions'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id), 'alpha_dash'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'employee_id' => ['nullable', 'string', 'max:30', Rule::unique('users')->ignore($user->id)],
            'role_id' => ['required', 'exists:roles,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'name.required' => 'Nama wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'role_id.required' => 'Role wajib dipilih.',
        ]);

        $oldValues = $user->only([
            'username', 'name', 'email', 'employee_id', 
            'role_id', 'unit_id', 'position_id', 'phone', 'is_active'
        ]);

        $user->update($validated);

        // Clear permission cache when role changes
        if ($oldValues['role_id'] !== $validated['role_id']) {
            $user->clearPermissionCache();
        }

        AuditLog::log(
            'updated',
            AuditLog::MODULE_USERS,
            'User',
            $user->id,
            $user->username,
            $oldValues,
            $validated,
            'Pengguna diperbarui.'
        );

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Check if user has documents
        if ($user->documents()->exists()) {
            return back()->with('error', 'Pengguna tidak dapat dihapus karena memiliki dokumen.');
        }

        $name = $user->name;
        
        AuditLog::log(
            'deleted',
            AuditLog::MODULE_USERS,
            'User',
            $user->id,
            $user->username,
            $user->toArray(),
            null,
            'Pengguna dihapus.'
        );

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna {$name} berhasil dihapus.");
    }

    /**
     * Reset user password
     */
    public function resetPassword(User $user)
    {
        $password = Str::random(12);
        
        $user->update([
            'password' => Hash::make($password),
            'must_change_password' => true,
        ]);

        AuditLog::log(
            'password_reset',
            AuditLog::MODULE_USERS,
            'User',
            $user->id,
            $user->username,
            null,
            null,
            'Password pengguna direset oleh administrator.'
        );

        return back()->with('success', "Password untuk {$user->name} berhasil direset. Password baru: {$password}");
    }

    /**
     * Toggle user active status
     */
    public function toggleActive(User $user)
    {
        // Prevent self-deactivation
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        AuditLog::log(
            $user->is_active ? 'activated' : 'deactivated',
            AuditLog::MODULE_USERS,
            'User',
            $user->id,
            $user->username,
            ['is_active' => !$user->is_active],
            ['is_active' => $user->is_active],
            "Pengguna {$status}."
        );

        return back()->with('success', "Pengguna {$user->name} berhasil {$status}.");
    }
}
