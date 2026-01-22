@extends('layouts.app')

@section('title', 'Manajemen Role')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Admin'],
        ['label' => 'Role']
    ]" />
@endsection

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Role</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">Kelola role dan hak akses pengguna</p>
        </div>
        <x-button href="{{ route('admin.roles.create') }}">
            <i class="bi bi-plus-lg"></i>
            Tambah Role
        </x-button>
    </div>

    {{-- Filters --}}
    <x-glass-card :hover="false" class="p-6">
        <form action="{{ route('admin.roles.index') }}" method="GET" class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-6">
                <label class="text-sm font-medium text-[var(--text-primary)]">Pencarian</label>
                <div class="relative mt-2">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]"></i>
                    <input type="text" class="glass-input pl-10" name="search"
                           value="{{ request('search') }}" placeholder="Cari nama role...">
                </div>
            </div>

            <div class="lg:col-span-3">
                <label class="text-sm font-medium text-[var(--text-primary)]">Status</label>
                <select name="active" class="glass-input mt-2">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div class="lg:col-span-3 flex flex-wrap items-end gap-2">
                <x-button type="submit" size="sm">
                    <i class="bi bi-funnel"></i>
                    Filter
                </x-button>
                <x-button href="{{ route('admin.roles.index') }}" size="sm" variant="secondary">
                    <i class="bi bi-x-lg"></i>
                </x-button>
            </div>
        </form>
    </x-glass-card>

    {{-- Roles Table --}}
    <x-table>
        <x-slot name="header">
            <th>Role</th>
            <th>Nama Sistem</th>
            <th>Level</th>
            <th>Jumlah Pengguna</th>
            <th>Status</th>
            <th class="text-right">Aksi</th>
        </x-slot>

        @forelse($roles as $role)
            <tr>
                <td>
                    <div class="text-sm font-semibold text-[var(--text-primary)]">{{ $role->display_name }}</div>
                    @if($role->description)
                        <div class="text-xs text-[var(--text-tertiary)]">{{ Str::limit($role->description, 50) }}</div>
                    @endif
                </td>
                <td class="text-xs font-mono text-[var(--text-tertiary)]">{{ $role->name }}</td>
                <td>
                    <x-badge type="default" size="sm">Level {{ $role->level }}</x-badge>
                </td>
                <td class="text-sm text-[var(--text-primary)]">{{ $role->users_count }} pengguna</td>
                <td>
                    @if($role->is_active)
                        <x-badge type="success" size="sm">Aktif</x-badge>
                    @else
                        <x-badge type="expired" size="sm">Nonaktif</x-badge>
                    @endif
                </td>
                <td class="text-right">
                    <x-dropdown align="right" width="56">
                        <x-slot name="trigger">
                            <button class="btn-ghost px-2 py-2 rounded-lg" type="button" aria-label="Aksi">
                                <i class="bi bi-three-dots"></i>
                            </button>
                        </x-slot>

                        <x-dropdown-item href="{{ route('admin.roles.show', $role) }}">
                            <i class="bi bi-eye text-[var(--text-tertiary)]"></i>
                            <span>Lihat</span>
                        </x-dropdown-item>
                        <x-dropdown-item href="{{ route('admin.roles.edit', $role) }}">
                            <i class="bi bi-pencil text-[var(--text-tertiary)]"></i>
                            <span>Edit</span>
                        </x-dropdown-item>
                        @if(!in_array($role->name, ['super_admin', 'admin']))
                            <div class="border-t border-[var(--surface-glass-border)] my-1"></div>
                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus role ini?')">
                                @csrf
                                @method('DELETE')
                                <x-dropdown-item type="submit" class="text-red-500 hover:text-red-600" {{ $role->users_count > 0 ? 'disabled' : '' }}>
                                    <i class="bi bi-trash"></i>
                                    <span>Hapus</span>
                                </x-dropdown-item>
                            </form>
                        @endif
                    </x-dropdown>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="py-10 text-center">
                    <div class="space-y-3 text-[var(--text-tertiary)]">
                        <i class="bi bi-shield text-3xl opacity-40"></i>
                        <p class="text-sm">Tidak ada role ditemukan.</p>
                    </div>
                </td>
            </tr>
        @endforelse

        @if($roles->hasPages())
            <x-slot name="pagination">
                {{ $roles->withQueryString()->links() }}
            </x-slot>
        @endif
    </x-table>
</div>
@endsection
