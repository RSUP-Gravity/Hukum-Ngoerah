@extends('layouts.app')

@section('title', $user->name)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Admin'],
        ['label' => 'Pengguna', 'url' => route('admin.users.index')],
        ['label' => $user->name]
    ]" />
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">{{ $user->name }}</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">Profil pengguna</p>
        </div>
        <x-button href="{{ route('admin.users.index') }}" variant="secondary">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </x-button>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main Info --}}
        <div class="space-y-6">
            <x-glass-card :hover="false" class="p-6 text-center">
                <div class="mx-auto mb-4 h-24 w-24 overflow-hidden rounded-full border border-[var(--surface-glass-border)] bg-[var(--surface-glass)]">
                    @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center text-3xl font-semibold text-[var(--text-primary)]">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <h2 class="text-lg font-semibold text-[var(--text-primary)]">{{ $user->name }}</h2>
                <p class="mt-1 text-sm text-[var(--text-tertiary)]">{{ $user->username }}</p>

                <div class="mt-3 flex flex-wrap justify-center gap-2">
                    <x-badge type="info" size="sm">{{ $user->role->display_name ?? 'No Role' }}</x-badge>
                    @if($user->is_active)
                        <x-badge type="success" size="sm">Aktif</x-badge>
                    @else
                        <x-badge type="expired" size="sm">Nonaktif</x-badge>
                    @endif
                </div>

                <div class="mt-5 flex flex-wrap justify-center gap-2">
                    @can('users.edit')
                        <x-button href="{{ route('admin.users.edit', $user) }}" size="sm" variant="secondary">
                            <i class="bi bi-pencil"></i>
                            Edit
                        </x-button>
                    @endcan
                    @can('users.reset_password')
                        <form action="{{ route('admin.users.reset-password', $user) }}" method="POST"
                              onsubmit="return confirm('Yakin ingin mereset password?')">
                            @csrf
                            <x-button type="submit" size="sm" variant="ghost">
                                <i class="bi bi-key"></i>
                                Reset Password
                            </x-button>
                        </form>
                    @endcan
                </div>
            </x-glass-card>

            <x-glass-card :hover="false" class="p-6">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]">Informasi Kontak</h3>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">Email</dt>
                        <dd class="text-right text-[var(--text-primary)]">{{ $user->email ?? '-' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">Telepon</dt>
                        <dd class="text-right text-[var(--text-primary)]">{{ $user->phone ?? '-' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">NIP</dt>
                        <dd class="text-right text-[var(--text-primary)]">{{ $user->employee_id ?? '-' }}</dd>
                    </div>
                </dl>
            </x-glass-card>

            <x-glass-card :hover="false" class="p-6">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]">Organisasi</h3>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">Unit</dt>
                        <dd class="text-right text-[var(--text-primary)]">{{ $user->unit->name ?? '-' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">Direktorat</dt>
                        <dd class="text-right text-[var(--text-primary)]">{{ $user->unit->directorate->name ?? '-' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">Jabatan</dt>
                        <dd class="text-right text-[var(--text-primary)]">{{ $user->position->name ?? '-' }}</dd>
                    </div>
                </dl>
            </x-glass-card>
        </div>

        {{-- Permissions & Activity --}}
        <div class="space-y-6 lg:col-span-2">
            <x-glass-card :hover="false" class="p-6">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]">Hak Akses</h3>
                <div class="mt-4">
                    @if($user->role && $user->role->permissions->isNotEmpty())
                        @php
                            $permissionsByModule = $user->role->permissions->groupBy('module');
                        @endphp
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            @foreach($permissionsByModule as $module => $permissions)
                                <div class="rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-3">
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-[var(--text-tertiary)]">{{ ucfirst($module) }}</h4>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($permissions as $permission)
                                            <x-badge type="default" size="sm">{{ $permission->display_name }}</x-badge>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-[var(--text-tertiary)]">Tidak ada hak akses khusus.</p>
                    @endif
                </div>
            </x-glass-card>

            <x-glass-card :hover="false" class="p-6">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]">Aktivitas Login</h3>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">Login Terakhir</dt>
                        <dd class="text-right text-[var(--text-primary)]">
                            @if($user->last_login_at)
                                {{ $user->last_login_at->format('d F Y H:i') }}
                                <span class="text-xs text-[var(--text-tertiary)]">({{ $user->last_login_at->diffForHumans() }})</span>
                            @else
                                <span class="text-[var(--text-tertiary)]">Belum pernah login</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">IP Terakhir</dt>
                        <dd class="text-right text-[var(--text-primary)]">{{ $user->last_login_ip ?? '-' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">Dibuat</dt>
                        <dd class="text-right text-[var(--text-primary)]">{{ $user->created_at->format('d F Y H:i') }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">Diperbarui</dt>
                        <dd class="text-right text-[var(--text-primary)]">{{ $user->updated_at->format('d F Y H:i') }}</dd>
                    </div>
                </dl>
            </x-glass-card>

            <x-glass-card :hover="false" class="p-6">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]">Aktivitas Terbaru</h3>
                <div class="mt-4 max-h-[400px] space-y-3 overflow-y-auto pr-2 custom-scrollbar">
                    @forelse($recentLogs as $log)
                        <div class="rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 text-xs text-[var(--text-tertiary)]">
                                        <x-badge type="default" size="sm">{{ strtoupper($log->action) }}</x-badge>
                                        <span class="text-[var(--text-primary)]">{{ $log->module }}</span>
                                        @if($log->entity_name)
                                            <span class="text-[var(--text-tertiary)]">- {{ $log->entity_name }}</span>
                                        @endif
                                    </div>
                                    @if($log->description)
                                        <div class="mt-2 text-xs text-[var(--text-tertiary)]">{{ $log->description }}</div>
                                    @endif
                                </div>
                                <div class="text-xs text-[var(--text-tertiary)]">{{ $log->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-[var(--surface-glass-border)] p-6 text-center text-xs text-[var(--text-tertiary)]">
                            Belum ada aktivitas tercatat
                        </div>
                    @endforelse
                </div>
            </x-glass-card>
        </div>
    </div>
</div>
@endsection
