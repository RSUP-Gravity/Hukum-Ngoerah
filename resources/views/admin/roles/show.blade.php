@extends('layouts.app')

@section('title', $role->display_name)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Admin'],
        ['label' => 'Role', 'url' => route('admin.roles.index')],
        ['label' => $role->display_name]
    ]" />
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">{{ $role->display_name }}</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">Detail role</p>
        </div>
        <x-button href="{{ route('admin.roles.index') }}" variant="secondary">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </x-button>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <x-glass-card :hover="false" class="p-6">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-[var(--text-primary)]">{{ $role->display_name }}</h2>
                        <div class="mt-1 text-xs font-mono text-[var(--text-tertiary)]">{{ $role->name }}</div>
                    </div>
                    @if($role->is_active)
                        <x-badge type="success" size="sm">Aktif</x-badge>
                    @else
                        <x-badge type="expired" size="sm">Nonaktif</x-badge>
                    @endif
                </div>

                @if($role->description)
                    <p class="mt-3 text-sm text-[var(--text-tertiary)]">{{ $role->description }}</p>
                @endif

                <div class="mt-4 flex items-center justify-between text-sm">
                    <span class="text-[var(--text-tertiary)]">Level {{ $role->level }}</span>
                    <x-button href="{{ route('admin.roles.edit', $role) }}" size="sm" variant="secondary">
                        <i class="bi bi-pencil"></i>
                        Edit
                    </x-button>
                </div>
            </x-glass-card>

            <x-glass-card :hover="false" class="p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-[var(--text-primary)]">Pengguna</h3>
                    <x-badge type="info" size="sm">{{ $role->users->count() }}</x-badge>
                </div>
                <div class="mt-4 max-h-[300px] space-y-2 overflow-y-auto pr-2 custom-scrollbar">
                    @forelse($role->users as $user)
                        <a href="{{ route('admin.users.show', $user) }}" class="flex items-center gap-3 rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] px-3 py-2 transition hover:border-[var(--surface-glass-border-hover)]">
                            <div class="h-8 w-8 overflow-hidden rounded-full border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] flex items-center justify-center text-xs font-semibold text-[var(--text-primary)]">
                                @if($user->avatar)
                                    <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                                @else
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                @endif
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-[var(--text-primary)]">{{ $user->name }}</div>
                                <div class="text-xs text-[var(--text-tertiary)]">{{ $user->username }}</div>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-lg border border-dashed border-[var(--surface-glass-border)] p-4 text-center text-xs text-[var(--text-tertiary)]">
                            Tidak ada pengguna dengan role ini
                        </div>
                    @endforelse
                </div>
            </x-glass-card>
        </div>

        <div class="lg:col-span-2">
            <x-glass-card :hover="false" class="p-6">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]">Hak Akses</h3>
                <div class="mt-4">
                    @if($role->name === 'super_admin')
                        <x-alert type="info" :dismissible="false">
                            Super Admin memiliki akses penuh ke seluruh sistem.
                        </x-alert>
                    @elseif($permissionsByModule->isEmpty())
                        <p class="text-sm text-[var(--text-tertiary)]">Tidak ada hak akses yang ditentukan.</p>
                    @else
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            @foreach($permissionsByModule as $module => $permissions)
                                <div class="rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-[var(--text-tertiary)]">{{ ucfirst($module) }}</h4>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($permissions as $permission)
                                            <x-badge type="info" size="sm">{{ $permission->display_name }}</x-badge>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-glass-card>
        </div>
    </div>
</div>
@endsection
