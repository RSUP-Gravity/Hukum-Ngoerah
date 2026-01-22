@extends('layouts.app')

@section('title', 'Audit Log')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Admin'],
        ['label' => 'Audit Log']
    ]" />
@endsection

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Audit Log</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">Riwayat aktivitas sistem</p>
        </div>
        <x-button type="button" variant="secondary" @click="$dispatch('open-modal', 'exportAuditLogModal')">
            <i class="bi bi-download"></i>
            Export
        </x-button>
    </div>

    {{-- Filters --}}
    <x-glass-card :hover="false" class="p-6">
        <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-3">
                <label class="text-sm font-medium text-[var(--text-primary)]">Pencarian</label>
                <div class="relative mt-2">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]"></i>
                    <input type="text" class="glass-input pl-10" name="search"
                           value="{{ request('search') }}" placeholder="Cari aktivitas...">
                </div>
            </div>

            <div class="lg:col-span-2">
                <label class="text-sm font-medium text-[var(--text-primary)]">Modul</label>
                <select name="module" class="glass-input mt-2">
                    <option value="">Semua Modul</option>
                    @foreach($modules as $module)
                        <option value="{{ $module }}" {{ request('module') === $module ? 'selected' : '' }}>
                            {{ ucfirst($module) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="text-sm font-medium text-[var(--text-primary)]">Aksi</label>
                <select name="action" class="glass-input mt-2">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                            {{ ucfirst($action) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="text-sm font-medium text-[var(--text-primary)]">Dari Tanggal</label>
                <input type="date" class="glass-input mt-2" name="date_from" value="{{ request('date_from') }}">
            </div>

            <div class="lg:col-span-2">
                <label class="text-sm font-medium text-[var(--text-primary)]">Sampai Tanggal</label>
                <input type="date" class="glass-input mt-2" name="date_to" value="{{ request('date_to') }}">
            </div>

            <div class="lg:col-span-1 flex items-end gap-2">
                <x-button type="submit" size="sm">
                    <i class="bi bi-funnel"></i>
                </x-button>
                <x-button href="{{ route('admin.audit-logs.index') }}" size="sm" variant="secondary">
                    <i class="bi bi-x-lg"></i>
                </x-button>
            </div>
        </form>
    </x-glass-card>

    {{-- Logs Table --}}
    <x-table>
        <x-slot name="header">
            <th>Waktu</th>
            <th>Pengguna</th>
            <th>Aksi</th>
            <th>Modul</th>
            <th>Entity</th>
            <th>Deskripsi</th>
            <th>IP</th>
            <th class="text-right">Detail</th>
        </x-slot>

        @forelse($logs as $log)
            <tr>
                <td class="text-sm text-[var(--text-secondary)]">
                    <span title="{{ $log->created_at->format('d/m/Y H:i:s') }}">
                        {{ $log->created_at->diffForHumans() }}
                    </span>
                </td>
                <td class="text-sm">
                    @if($log->user)
                        <a href="{{ route('admin.users.show', $log->user) }}" class="text-[var(--text-primary)] hover:text-primary-400">
                            {{ $log->user->name }}
                        </a>
                    @else
                        <span class="text-[var(--text-tertiary)]">{{ $log->username ?? 'System' }}</span>
                    @endif
                </td>
                <td>
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
                    <x-badge :type="$actionColors[$log->action] ?? 'default'" size="sm">
                        {{ strtoupper($log->action) }}
                    </x-badge>
                </td>
                <td class="text-sm text-[var(--text-primary)]">{{ ucfirst($log->module) }}</td>
                <td class="text-sm text-[var(--text-primary)]">{{ Str::limit($log->entity_name, 30) ?? '-' }}</td>
                <td class="text-sm text-[var(--text-secondary)]">{{ Str::limit($log->description, 40) ?? '-' }}</td>
                <td class="text-xs font-mono text-[var(--text-tertiary)]">{{ $log->ip_address ?? '-' }}</td>
                <td class="text-right">
                    <x-button href="{{ route('admin.audit-logs.show', $log) }}" size="sm" variant="secondary">
                        <i class="bi bi-eye"></i>
                    </x-button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="py-10 text-center">
                    <div class="space-y-3 text-[var(--text-tertiary)]">
                        <i class="bi bi-journal-text text-3xl opacity-40"></i>
                        <p class="text-sm">Tidak ada log ditemukan.</p>
                    </div>
                </td>
            </tr>
        @endforelse

        @if($logs->hasPages())
            <x-slot name="pagination">
                {{ $logs->withQueryString()->links() }}
            </x-slot>
        @endif
    </x-table>
</div>

{{-- Export Modal --}}
<x-modal name="exportAuditLogModal" maxWidth="lg">
    <x-slot name="header">
        <h3 class="text-lg font-semibold text-[var(--text-primary)]">Export Audit Log</h3>
    </x-slot>

    <form action="{{ route('admin.audit-logs.export') }}" method="GET" class="space-y-4">
        <x-input type="date" name="date_from" label="Dari Tanggal" :required="true" value="{{ date('Y-m-01') }}" />
        <x-input type="date" name="date_to" label="Sampai Tanggal" :required="true" value="{{ date('Y-m-d') }}" />
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-[var(--text-primary)]">Modul</label>
            <select name="module" class="glass-input">
                <option value="">Semua Modul</option>
                @foreach($modules as $module)
                    <option value="{{ $module }}">{{ ucfirst($module) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col-reverse gap-2 border-t border-[var(--surface-glass-border)] pt-4 sm:flex-row sm:justify-end">
            <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'exportAuditLogModal')">Batal</x-button>
            <x-button type="submit">
                <i class="bi bi-download"></i>
                Export CSV
            </x-button>
        </div>
    </form>
</x-modal>
@endsection
