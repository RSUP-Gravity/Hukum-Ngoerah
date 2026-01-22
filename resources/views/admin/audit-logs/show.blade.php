@extends('layouts.app')

@section('title', 'Detail Audit Log')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Admin'],
        ['label' => 'Audit Log', 'url' => route('admin.audit-logs.index')],
        ['label' => 'Detail']
    ]" />
@endsection

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Detail Audit Log</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $auditLog->created_at->format('d F Y H:i:s') }}</p>
        </div>
        <x-button href="{{ route('admin.audit-logs.index') }}" variant="secondary">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </x-button>
    </div>

    {{-- Log Info --}}
    <x-glass-card :hover="false" class="p-6">
        <h2 class="text-sm font-semibold text-[var(--text-primary)]">Informasi Log</h2>
        <dl class="mt-4 grid grid-cols-1 gap-4 text-sm text-[var(--text-secondary)] sm:grid-cols-2">
            <div>
                <dt class="text-xs uppercase tracking-wide text-[var(--text-tertiary)]">Waktu</dt>
                <dd class="mt-1 text-[var(--text-primary)]">{{ $auditLog->created_at->format('d F Y H:i:s') }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-[var(--text-tertiary)]">Pengguna</dt>
                <dd class="mt-1">
                    @if($auditLog->user)
                        <a href="{{ route('admin.users.show', $auditLog->user) }}" class="text-[var(--text-primary)] hover:text-primary-400">
                            {{ $auditLog->user->name }}
                        </a>
                        <span class="text-[var(--text-tertiary)]">({{ $auditLog->username }})</span>
                    @else
                        <span class="text-[var(--text-primary)]">{{ $auditLog->username ?? 'System' }}</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-[var(--text-tertiary)]">Aksi</dt>
                <dd class="mt-1">
                    @php
                        $actionColors = [
                            'created' => 'success',
                            'updated' => 'info',
                            'deleted' => 'critical',
                            'login' => 'info',
                            'logout' => 'default',
                            'approved' => 'success',
                            'rejected' => 'critical',
                        ];
                    @endphp
                    <x-badge :type="$actionColors[$auditLog->action] ?? 'default'" size="sm">
                        {{ strtoupper($auditLog->action) }}
                    </x-badge>
                </dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-[var(--text-tertiary)]">Modul</dt>
                <dd class="mt-1 text-[var(--text-primary)]">{{ ucfirst($auditLog->module) }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-[var(--text-tertiary)]">Entity Type</dt>
                <dd class="mt-1 text-[var(--text-primary)]">{{ $auditLog->entity_type ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-[var(--text-tertiary)]">Entity ID</dt>
                <dd class="mt-1 text-[var(--text-primary)]">{{ $auditLog->entity_id ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-[var(--text-tertiary)]">Entity Name</dt>
                <dd class="mt-1 text-[var(--text-primary)]">{{ $auditLog->entity_name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-[var(--text-tertiary)]">IP Address</dt>
                <dd class="mt-1 text-xs font-mono text-[var(--text-tertiary)]">{{ $auditLog->ip_address ?? '-' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs uppercase tracking-wide text-[var(--text-tertiary)]">User Agent</dt>
                <dd class="mt-1 text-xs text-[var(--text-tertiary)]">{{ $auditLog->user_agent ?? '-' }}</dd>
            </div>
        </dl>
    </x-glass-card>

    {{-- Description --}}
    @if($auditLog->description)
        <x-glass-card :hover="false" class="p-6">
            <h2 class="text-sm font-semibold text-[var(--text-primary)]">Deskripsi</h2>
            <p class="mt-3 text-sm text-[var(--text-secondary)]">{{ $auditLog->description }}</p>
        </x-glass-card>
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        {{-- Old Values --}}
        <x-glass-card :hover="false" class="p-6">
            <h2 class="text-sm font-semibold text-[var(--text-primary)]">
                <i class="bi bi-arrow-left-circle text-rose-400"></i>
                Nilai Sebelumnya
            </h2>
            <div class="mt-4">
                @if($auditLog->old_values)
                    <pre class="max-h-96 overflow-auto rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4 text-xs text-[var(--text-secondary)]"><code>{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                @else
                    <p class="text-sm text-[var(--text-tertiary)]">Tidak ada data sebelumnya.</p>
                @endif
            </div>
        </x-glass-card>

        {{-- New Values --}}
        <x-glass-card :hover="false" class="p-6">
            <h2 class="text-sm font-semibold text-[var(--text-primary)]">
                <i class="bi bi-arrow-right-circle text-emerald-400"></i>
                Nilai Sesudahnya
            </h2>
            <div class="mt-4">
                @if($auditLog->new_values)
                    <pre class="max-h-96 overflow-auto rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4 text-xs text-[var(--text-secondary)]"><code>{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                @else
                    <p class="text-sm text-[var(--text-tertiary)]">Tidak ada data sesudahnya.</p>
                @endif
            </div>
        </x-glass-card>
    </div>
</div>
@endsection
