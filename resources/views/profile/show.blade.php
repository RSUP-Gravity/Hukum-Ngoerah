@extends('layouts.app')

@section('title', 'Profil Saya')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Profil']
    ]" />
@endsection

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Profile Info --}}
        <div class="lg:col-span-4">
            <x-glass-card :hover="false" class="p-6">
                <div class="text-center">
                    <div class="mx-auto mb-4 h-28 w-28">
                        @if($user->avatar)
                            <img src="{{ Storage::url($user->avatar) }}"
                                 class="h-28 w-28 rounded-full object-cover">
                        @else
                            <div class="flex h-28 w-28 items-center justify-center rounded-full bg-primary-500/20 text-3xl font-semibold text-primary-300">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <h2 class="text-lg font-semibold text-[var(--text-primary)]">{{ $user->name }}</h2>
                    <p class="text-sm text-[var(--text-tertiary)]">{{ $user->nip }}</p>
                    <div class="mt-3">
                        <x-badge type="info" size="sm">{{ $user->role->display_name ?? $user->role->name }}</x-badge>
                    </div>
                </div>
                <div class="mt-6 space-y-3 border-t border-[var(--surface-glass-border)] pt-4">
                    <div class="flex items-center gap-3 text-sm text-[var(--text-secondary)]">
                        <i class="bi bi-envelope text-primary-400"></i>
                        <span>{{ $user->email ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-[var(--text-secondary)]">
                        <i class="bi bi-telephone text-primary-400"></i>
                        <span>{{ $user->phone ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-[var(--text-secondary)]">
                        <i class="bi bi-building text-primary-400"></i>
                        <span>{{ $user->unit->directorate->name ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-[var(--text-secondary)]">
                        <i class="bi bi-diagram-3 text-primary-400"></i>
                        <span>{{ $user->unit->name ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-[var(--text-secondary)]">
                        <i class="bi bi-person-badge text-primary-400"></i>
                        <span>{{ $user->position->name ?? '-' }}</span>
                    </div>
                </div>
                <div class="mt-6">
                    <x-button href="{{ route('profile.edit') }}" class="w-full">
                        <i class="bi bi-pencil"></i>
                        Edit Profil
                    </x-button>
                </div>
            </x-glass-card>
        </div>

        {{-- Details --}}
        <div class="lg:col-span-8 space-y-6">
            {{-- Login Activity --}}
            <x-glass-card :hover="false" class="p-6">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]"><i class="bi bi-clock-history"></i> Aktivitas Login</h3>
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                        <p class="text-xs text-[var(--text-tertiary)]">Login Terakhir</p>
                        <p class="mt-2 text-sm font-semibold text-[var(--text-primary)]">{{ $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : '-' }}</p>
                    </div>
                    <div class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                        <p class="text-xs text-[var(--text-tertiary)]">IP Address</p>
                        <p class="mt-2 text-xs font-mono text-[var(--text-secondary)]">{{ $user->last_login_ip ?? '-' }}</p>
                    </div>
                    <div class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                        <p class="text-xs text-[var(--text-tertiary)]">Bergabung</p>
                        <p class="mt-2 text-sm font-semibold text-[var(--text-primary)]">{{ $user->created_at->format('d M Y') }}</p>
                    </div>
                </div>
            </x-glass-card>

            {{-- Permissions --}}
            <x-glass-card :hover="false" class="p-6">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]"><i class="bi bi-shield-check"></i> Hak Akses</h3>
                <div class="mt-4 flex flex-wrap gap-2">
                    @if($user->role && $user->role->permissions)
                        @foreach($user->role->permissions as $permission)
                            <x-badge type="default" size="sm">
                                {{ $permission->display_name ?? $permission->name }}
                            </x-badge>
                        @endforeach
                    @else
                        <span class="text-sm text-[var(--text-tertiary)]">Tidak ada hak akses khusus.</span>
                    @endif
                </div>
            </x-glass-card>

            {{-- Quick Actions --}}
            <x-glass-card :hover="false" class="p-6">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]"><i class="bi bi-lightning"></i> Aksi Cepat</h3>
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <x-button href="{{ route('profile.edit') }}" variant="secondary" class="w-full">
                        <i class="bi bi-pencil"></i>
                        Edit Profil
                    </x-button>
                    <x-button href="{{ route('password.change') }}" variant="secondary" class="w-full">
                        <i class="bi bi-key"></i>
                        Ganti Password
                    </x-button>
                    <x-button href="{{ route('documents.index') }}?created_by={{ $user->id }}" variant="secondary" class="w-full">
                        <i class="bi bi-file-earmark-text"></i>
                        Dokumen Saya
                    </x-button>
                    <x-button href="{{ route('notifications.index') }}" variant="secondary" class="w-full">
                        <i class="bi bi-bell"></i>
                        Notifikasi
                    </x-button>
                </div>
            </x-glass-card>
        </div>
    </div>
</div>
@endsection
