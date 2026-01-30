@extends('layouts.app')

@section('title', 'User Analytic')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">User Analytic</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">Ringkasan kinerja unggah dokumen per pengguna</p>
        </div>
    </div>

    @php
        $hasFilters = request()->hasAny(['search', 'role_id', 'unit_id', 'date_from', 'date_to', 'active']);
    @endphp
    <x-glass-card :hover="false" class="p-6 overflow-visible z-40" x-data="autoFilter()">
        <form action="{{ route('admin.user-analytics.index') }}" method="GET" id="filterForm" class="space-y-4" x-ref="form">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <label class="text-sm font-medium text-[var(--text-primary)]">Pencarian</label>
                <div class="relative mt-2">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]"></i>
                    <input type="text" class="glass-input pl-10" name="search"
                           value="{{ request('search') }}" placeholder="Cari nama, username, email, atau NIP...">
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

            <div class="lg:col-span-2 flex flex-col gap-2 lg:items-end">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 text-xs text-[var(--text-tertiary)]">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        Auto diterapkan
                    </span>
                    <button type="submit" class="sr-only">Terapkan</button>
                </div>
                <div class="inline-flex items-center gap-2">
                    <a href="{{ route('admin.user-analytics.index') }}"
                       class="btn-secondary px-3 py-2 rounded-lg {{ $hasFilters ? '' : 'pointer-events-none opacity-50' }}"
                       aria-label="Reset filter" title="Reset filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <span class="sr-only">Reset</span>
                    </a>
                </div>
            </div>

            <div class="lg:col-span-3">
                <label class="text-sm font-medium text-[var(--text-primary)]">Tanggal Mulai</label>
                <input type="date" name="date_from" class="glass-input mt-2" value="{{ request('date_from') }}">
            </div>

            <div class="lg:col-span-3">
                <label class="text-sm font-medium text-[var(--text-primary)]">Tanggal Akhir</label>
                <input type="date" name="date_to" class="glass-input mt-2" value="{{ request('date_to') }}">
            </div>

            <div class="lg:col-span-6 flex items-end">
                <span class="text-xs text-[var(--text-tertiary)]">Periode dihitung berdasarkan tanggal unggah dokumen.</span>
            </div>
            </div>
        </form>
    </x-glass-card>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
        <x-glass-card class="p-4">
            <p class="text-xs uppercase tracking-wide text-[var(--text-tertiary)]">Total Pengguna</p>
            <p class="mt-2 text-2xl font-semibold text-[var(--text-primary)]">{{ number_format($summary['total_users']) }}</p>
        </x-glass-card>
        <x-glass-card class="p-4">
            <p class="text-xs uppercase tracking-wide text-[var(--text-tertiary)]">Dokumen Diunggah</p>
            <p class="mt-2 text-2xl font-semibold text-[var(--text-primary)]">{{ number_format($summary['uploaded']) }}</p>
        </x-glass-card>
        <x-glass-card class="p-4">
            <p class="text-xs uppercase tracking-wide text-[var(--text-tertiary)]">Disetujui</p>
            <p class="mt-2 text-2xl font-semibold text-[var(--text-primary)]">{{ number_format($summary['approved']) }}</p>
        </x-glass-card>
        <x-glass-card class="p-4">
            <p class="text-xs uppercase tracking-wide text-[var(--text-tertiary)]">Sedang Review</p>
            <p class="mt-2 text-2xl font-semibold text-[var(--text-primary)]">{{ number_format($summary['in_review']) }}</p>
        </x-glass-card>
        <x-glass-card class="p-4">
            <p class="text-xs uppercase tracking-wide text-[var(--text-tertiary)]">Ditolak</p>
            <p class="mt-2 text-2xl font-semibold text-[var(--text-primary)]">{{ number_format($summary['rejected']) }}</p>
        </x-glass-card>
        <x-glass-card class="p-4">
            <p class="text-xs uppercase tracking-wide text-[var(--text-tertiary)]">Revisi</p>
            <p class="mt-2 text-2xl font-semibold text-[var(--text-primary)]">{{ number_format($summary['revisions']) }}</p>
        </x-glass-card>
    </div>

    <x-glass-card :hover="false" class="p-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-[var(--text-primary)]">Tren Kinerja</h3>
                <p class="text-xs text-[var(--text-tertiary)]">Periode: {{ $chartRangeLabel }}</p>
            </div>
        </div>
        <div class="mt-4 h-72">
            <canvas id="user-analytic-trend"></canvas>
        </div>
    </x-glass-card>

    <x-table>
        <x-slot name="header">
            <th>Pengguna</th>
            <th>Role</th>
            <th>Unit</th>
            <th class="text-center">Diunggah</th>
            <th class="text-center">Disetujui</th>
            <th class="text-center">Review</th>
            <th class="text-center">Ditolak</th>
            <th class="text-center">Revisi</th>
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
                            <div class="text-xs text-[var(--text-tertiary)]">{{ $user->username }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <x-badge type="info" size="sm">{{ $user->role?->display_name ?? '-' }}</x-badge>
                </td>
                <td class="text-sm text-[var(--text-primary)]">{{ $user->unit?->name ?? '-' }}</td>
                <td class="text-center text-sm text-[var(--text-primary)]">{{ number_format($user->uploaded_count ?? 0) }}</td>
                <td class="text-center text-sm text-[var(--text-primary)]">{{ number_format($user->approved_count ?? 0) }}</td>
                <td class="text-center text-sm text-[var(--text-primary)]">{{ number_format($user->in_review_count ?? 0) }}</td>
                <td class="text-center text-sm text-[var(--text-primary)]">{{ number_format($user->rejected_count ?? 0) }}</td>
                <td class="text-center text-sm text-[var(--text-primary)]">{{ number_format($user->revision_count ?? 0) }}</td>
                <td class="text-right">
                    <x-button href="{{ route('admin.user-analytics.show', $user) }}" size="sm" variant="secondary">
                        Detail
                    </x-button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="py-10 text-center">
                    <div class="space-y-3 text-[var(--text-tertiary)]">
                        <i class="bi bi-people text-3xl opacity-40"></i>
                        <p class="text-sm">Tidak ada data pengguna ditemukan.</p>
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

@push('scripts')
<script>
    function autoFilter(config = {}) {
        return {
            submitTimer: null,

            init() {
                this.bindAutoApply();
            },

            bindAutoApply() {
                const form = this.$refs.form;
                if (!form) return;

                const fields = form.querySelectorAll('input, select, textarea');
                fields.forEach((field) => {
                    if (!field.name) return;
                    if (field.dataset.noAutoSubmit !== undefined) return;

                    const isTextInput = field.tagName === 'INPUT' && ['text', 'search'].includes(field.type);
                    const handler = (event) => this.queueSubmit(event);

                    field.addEventListener(isTextInput ? 'input' : 'change', handler);

                    if (isTextInput) {
                        field.addEventListener('change', handler);
                    }
                });
            },

            queueSubmit(event) {
                const target = event?.target;
                if (!target) return;

                if (target.name === 'search') {
                    const value = target.value.trim();
                    if (value.length === 1) {
                        return;
                    }
                }

                const delay = event.type === 'input' ? 500 : 150;
                clearTimeout(this.submitTimer);
                this.submitTimer = setTimeout(() => {
                    this.$refs.form?.submit();
                }, delay);
            },
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('user-analytic-trend');
        if (!ctx || !window.Chart) return;

        const chartData = @json($chartData);
        const dark = document.documentElement.classList.contains('dark');
        const textColor = dark ? '#E2E8F0' : '#0F172A';
        const gridColor = dark ? 'rgba(148, 163, 184, 0.15)' : 'rgba(148, 163, 184, 0.28)';

        Chart.defaults.color = textColor;
        Chart.defaults.borderColor = gridColor;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Diunggah',
                        data: chartData.uploads,
                        borderColor: '#0EA5E9',
                        backgroundColor: 'rgba(14, 165, 233, 0.15)',
                        tension: 0.35,
                        fill: true
                    },
                    {
                        label: 'Disetujui',
                        data: chartData.approvals,
                        borderColor: '#22C55E',
                        backgroundColor: 'rgba(34, 197, 94, 0.15)',
                        tension: 0.35,
                        fill: true
                    },
                    {
                        label: 'Ditolak',
                        data: chartData.rejections,
                        borderColor: '#EF4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.15)',
                        tension: 0.35,
                        fill: true
                    },
                    {
                        label: 'Revisi',
                        data: chartData.revisions,
                        borderColor: '#F59E0B',
                        backgroundColor: 'rgba(245, 158, 11, 0.15)',
                        tension: 0.35,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: gridColor },
                        ticks: { color: textColor }
                    },
                    y: {
                        grid: { color: gridColor },
                        ticks: { color: textColor },
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
@endpush
