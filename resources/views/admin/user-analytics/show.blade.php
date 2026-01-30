@extends('layouts.app')

@section('title', 'User Analytic Detail')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">User Analytic</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">
                Detail aktivitas {{ $user->name }} ({{ $user->role?->display_name ?? '-' }})
            </p>
        </div>
        <x-button href="{{ route('admin.user-analytics.index') }}" variant="secondary">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </x-button>
    </div>

    <x-glass-card :hover="false" class="p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-full overflow-hidden bg-[var(--surface-glass)] border border-[var(--surface-glass-border)] flex items-center justify-center text-sm font-semibold text-[var(--text-primary)]">
                    @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </div>
                <div>
                    <p class="text-sm font-semibold text-[var(--text-primary)]">{{ $user->name }}</p>
                    <p class="text-xs text-[var(--text-tertiary)]">{{ $user->unit?->name ?? 'Tanpa unit' }}</p>
                </div>
            </div>
            <div class="text-sm text-[var(--text-tertiary)]">
                Username: <span class="font-medium text-[var(--text-primary)]">{{ $user->username }}</span>
            </div>
        </div>
    </x-glass-card>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
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

    @php
        $hasFilters = request()->hasAny(['date_from', 'date_to', 'action']);
    @endphp
    <x-glass-card :hover="false" class="p-6 overflow-visible z-40" x-data="autoFilter()">
        <form action="{{ route('admin.user-analytics.show', $user) }}" method="GET" id="filterForm" class="space-y-4" x-ref="form">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-3">
                <label class="text-sm font-medium text-[var(--text-primary)]">Tanggal Mulai</label>
                <input type="date" name="date_from" class="glass-input mt-2" value="{{ request('date_from') }}">
            </div>

            <div class="lg:col-span-3">
                <label class="text-sm font-medium text-[var(--text-primary)]">Tanggal Akhir</label>
                <input type="date" name="date_to" class="glass-input mt-2" value="{{ request('date_to') }}">
            </div>

            <div class="lg:col-span-3">
                <label class="text-sm font-medium text-[var(--text-primary)]">Aksi</label>
                <select name="action" class="glass-input mt-2">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $key => $label)
                        <option value="{{ $key }}" {{ request('action') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-3 flex flex-col gap-2 lg:items-end">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 text-xs text-[var(--text-tertiary)]">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        Auto diterapkan
                    </span>
                    <button type="submit" class="sr-only">Terapkan</button>
                </div>
                <div class="inline-flex items-center gap-2">
                    <a href="{{ route('admin.user-analytics.show', $user) }}"
                       class="btn-secondary px-3 py-2 rounded-lg {{ $hasFilters ? '' : 'pointer-events-none opacity-50' }}"
                       aria-label="Reset filter" title="Reset filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <span class="sr-only">Reset</span>
                    </a>
                </div>
            </div>
            </div>
        </form>
    </x-glass-card>

    <x-table>
        <x-slot name="header">
            <th>Waktu</th>
            <th>Dokumen</th>
            <th>Aksi</th>
            <th>Keterangan</th>
        </x-slot>

        @forelse($history as $row)
            <tr>
                <td class="text-xs text-[var(--text-tertiary)]">
                    {{ $row->created_at?->format('d/m/Y H:i') ?? '-' }}
                </td>
                <td>
                    @if($row->document)
                        <a href="{{ route('documents.show', $row->document) }}" class="text-sm text-primary-500 hover:text-primary-600">
                            {{ $row->document->title }}
                        </a>
                        <div class="text-xs text-[var(--text-tertiary)]">{{ $row->document->document_number }}</div>
                    @else
                        <span class="text-sm text-[var(--text-tertiary)]">Dokumen tidak ditemukan</span>
                    @endif
                </td>
                <td class="text-sm text-[var(--text-primary)]">
                    {{ $row->action_label ?? $row->action }}
                </td>
                <td class="text-xs text-[var(--text-tertiary)]">
                    {{ $row->description ?? $row->notes ?? '-' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="py-10 text-center">
                    <div class="space-y-3 text-[var(--text-tertiary)]">
                        <i class="bi bi-clock-history text-3xl opacity-40"></i>
                        <p class="text-sm">Tidak ada aktivitas ditemukan.</p>
                    </div>
                </td>
            </tr>
        @endforelse

        @if($history->hasPages())
            <x-slot name="pagination">
                {{ $history->withQueryString()->links() }}
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
</script>
@endpush
