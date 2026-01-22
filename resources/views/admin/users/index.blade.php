@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Admin'],
        ['label' => 'Pengguna']
    ]" />
@endsection

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Pengguna</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">Kelola akun pengguna sistem</p>
        </div>
        @can('users.create')
            <x-button href="{{ route('admin.users.create') }}">
                <i class="bi bi-plus-lg"></i>
                Tambah Pengguna
            </x-button>
        @endcan
    </div>

    {{-- Filters --}}
    <x-glass-card :hover="false" class="p-6">
        <form action="{{ route('admin.users.index') }}" method="GET" class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <label class="text-sm font-medium text-[var(--text-primary)]">Pencarian</label>
                <div class="relative mt-2">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]"></i>
                    <input type="text" class="glass-input pl-10" name="search"
                           value="{{ request('search') }}" placeholder="Cari nama, username, atau email...">
                </div>
            </div>

            <div class="lg:col-span-2">
                <label class="text-sm font-medium text-[var(--text-primary)]">Role</label>
                <select name="role_id" class="glass-input mt-2">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                            {{ $role->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="text-sm font-medium text-[var(--text-primary)]">Unit</label>
                <select name="unit_id" class="glass-input mt-2">
                    <option value="">Semua Unit</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                            {{ $unit->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="text-sm font-medium text-[var(--text-primary)]">Status</label>
                <select name="active" class="glass-input mt-2">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div class="lg:col-span-2 flex flex-wrap items-end gap-2">
                <x-button type="submit" size="sm">
                    <i class="bi bi-funnel"></i>
                    Filter
                </x-button>
                <x-button href="{{ route('admin.users.index') }}" size="sm" variant="secondary">
                    <i class="bi bi-x-lg"></i>
                </x-button>
            </div>
        </form>
    </x-glass-card>

    {{-- Users Table --}}
    <x-table>
        <x-slot name="header">
            <th>Pengguna</th>
            <th>Username</th>
            <th>Role</th>
            <th>Unit</th>
            <th>Jabatan</th>
            <th>Status</th>
            <th>Login Terakhir</th>
            <th class="text-right">Aksi</th>
        </x-slot>

        @forelse($users as $user)
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-full overflow-hidden bg-[var(--surface-glass)] border border-[var(--surface-glass-border)] flex items-center justify-center text-xs font-semibold text-[var(--text-primary)]">
                            @if($user->avatar)
                                <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                            @else
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            @endif
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-[var(--text-primary)]">{{ $user->name }}</div>
                            @if($user->email)
                                <div class="text-xs text-[var(--text-tertiary)]">{{ $user->email }}</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="text-xs font-mono text-[var(--text-tertiary)]">{{ $user->username }}</td>
                <td>
                    <x-badge type="info" size="sm">{{ $user->role->display_name ?? '-' }}</x-badge>
                </td>
                <td class="text-sm text-[var(--text-primary)]">{{ $user->unit->name ?? '-' }}</td>
                <td class="text-sm text-[var(--text-primary)]">{{ $user->position->name ?? '-' }}</td>
                <td>
                    @if($user->is_active)
                        <x-badge type="success" size="sm">Aktif</x-badge>
                    @else
                        <x-badge type="expired" size="sm">Nonaktif</x-badge>
                    @endif
                </td>
                <td class="text-xs text-[var(--text-tertiary)]">
                    @if($user->last_login_at)
                        <span title="{{ $user->last_login_at->format('d/m/Y H:i') }}">{{ $user->last_login_at->diffForHumans() }}</span>
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">
                    <x-dropdown align="right" width="56">
                        <x-slot name="trigger">
                            <button class="btn-ghost px-2 py-2 rounded-lg" type="button" aria-label="Aksi">
                                <i class="bi bi-three-dots"></i>
                            </button>
                        </x-slot>

                        <x-dropdown-item href="{{ route('admin.users.show', $user) }}">
                            <i class="bi bi-eye text-[var(--text-tertiary)]"></i>
                            <span>Lihat</span>
                        </x-dropdown-item>
                        @can('users.edit')
                            <x-dropdown-item href="{{ route('admin.users.edit', $user) }}">
                                <i class="bi bi-pencil text-[var(--text-tertiary)]"></i>
                                <span>Edit</span>
                            </x-dropdown-item>
                        @endcan
                        @can('users.reset_password')
                            <form action="{{ route('admin.users.reset-password', $user) }}" method="POST"
                                  onsubmit="return confirm('Yakin ingin mereset password pengguna ini?')">
                                @csrf
                                <x-dropdown-item type="submit">
                                    <i class="bi bi-key text-[var(--text-tertiary)]"></i>
                                    <span>Reset Password</span>
                                </x-dropdown-item>
                            </form>
                        @endcan
                        @can('users.edit')
                            @if($user->id !== auth()->id())
                                <div class="border-t border-[var(--surface-glass-border)] my-1"></div>
                                <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST">
                                    @csrf
                                    <x-dropdown-item type="submit">
                                        @if($user->is_active)
                                            <i class="bi bi-person-x text-[var(--text-tertiary)]"></i>
                                            <span>Nonaktifkan</span>
                                        @else
                                            <i class="bi bi-person-check text-[var(--text-tertiary)]"></i>
                                            <span>Aktifkan</span>
                                        @endif
                                    </x-dropdown-item>
                                </form>
                            @endif
                        @endcan
                        @can('users.delete')
                            @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-dropdown-item type="submit" class="text-red-500 hover:text-red-600">
                                        <i class="bi bi-trash"></i>
                                        <span>Hapus</span>
                                    </x-dropdown-item>
                                </form>
                            @endif
                        @endcan
                    </x-dropdown>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="py-10 text-center">
                    <div class="space-y-3 text-[var(--text-tertiary)]">
                        <i class="bi bi-people text-3xl opacity-40"></i>
                        <p class="text-sm">Tidak ada pengguna ditemukan.</p>
                        @can('users.create')
                            <x-button href="{{ route('admin.users.create') }}" size="sm">
                                <i class="bi bi-plus-lg"></i>
                                Tambah Pengguna
                            </x-button>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforelse

        @if($users->hasPages())
            <x-slot name="pagination">
                {{ $users->withQueryString()->links() }}
            </x-slot>
        @endif
    </x-table>
</div>
@endsection
