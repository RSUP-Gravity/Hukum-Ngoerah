<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the profile edit form
     */
    public function edit()
    {
        $user = Auth::user()->load(['role', 'unit.directorate', 'position']);
        
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
        ]);

        $oldValues = $user->only(['name', 'email', 'phone']);
        
        $user->update($validated);

        AuditLog::log(
            'profile_updated',
            AuditLog::MODULE_USERS,
            'User',
            $user->id,
            $user->username,
            $oldValues,
            $validated,
            'Profil diperbarui.'
        );

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Update the user's avatar
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'avatar.required' => 'Foto wajib dipilih.',
            'avatar.image' => 'File harus berupa gambar.',
            'avatar.mimes' => 'Format gambar harus JPG, JPEG, atau PNG.',
            'avatar.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        $user = Auth::user();

        // Delete old avatar if exists
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        
        $user->update(['avatar' => $path]);

        AuditLog::log(
            'avatar_updated',
            AuditLog::MODULE_USERS,
            'User',
            $user->id,
            $user->username,
            null,
            null,
            'Foto profil diperbarui.'
        );

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }
}
