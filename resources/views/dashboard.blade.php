<x-layouts.app title="Dashboard">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[var(--text-primary)]">Dashboard</h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">
                    Selamat datang, {{ auth()->user()->name ?? 'User' }}! Berikut ringkasan dokumen hukum Anda.
                </p>
            </div>
            <x-button href="{{ route('documents.create') }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Dokumen
            </x-button>
        </div>
    </x-slot>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Dokumen -->
        <x-glass-card class="relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-[var(--text-secondary)]">Total Dokumen</p>
                    <p class="mt-2 text-3xl font-bold text-[var(--text-primary)]">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-primary-500/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-500 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                    </svg>
                    {{ $stats['new_this_month'] ?? 0 }}
                </span>
                <span class="text-[var(--text-tertiary)] ml-2">dokumen baru bulan ini</span>
            </div>
        </x-glass-card>

        <!-- Dokumen Aktif -->
        <x-glass-card class="relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-[var(--text-secondary)]">Dokumen Aktif</p>
                    <p class="mt-2 text-3xl font-bold text-[var(--text-primary)]">{{ $stats['active'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-[var(--surface-glass-border)] rounded-full h-2">
                    <div 
                        class="bg-green-500 h-2 rounded-full transition-all duration-500" 
                        style="width: {{ ($stats['total'] ?? 1) > 0 ? (($stats['active'] ?? 0) / ($stats['total'] ?? 1)) * 100 : 0 }}%"
                    ></div>
                </div>
            </div>
        </x-glass-card>

        <!-- Akan Kadaluarsa -->
        <x-glass-card class="relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-[var(--text-secondary)]">Akan Kadaluarsa</p>
                    <p class="mt-2 text-3xl font-bold text-yellow-500">{{ $stats['expiring'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-yellow-500/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <a href="{{ route('documents.index', ['status' => 'expiring']) }}" class="mt-4 inline-flex items-center text-sm text-yellow-500 hover:text-yellow-600 transition-colors">
                Lihat detail
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </x-glass-card>

        <!-- Kadaluarsa -->
        <x-glass-card class="relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-[var(--text-secondary)]">Kadaluarsa</p>
                    <p class="mt-2 text-3xl font-bold text-red-500">{{ $stats['expired'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-red-500/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>
            <a href="{{ route('documents.index', ['status' => 'expired']) }}" class="mt-4 inline-flex items-center text-sm text-red-500 hover:text-red-600 transition-colors">
                Lihat detail
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </x-glass-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart: Dokumen per Jenis -->
        <x-glass-card :hover="false" class="lg:col-span-2">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-[var(--text-primary)]">Dokumen per Jenis</h3>
                <x-dropdown align="right">
                    <x-slot name="trigger">
                        <button class="btn-ghost p-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                    </x-slot>
                    <x-dropdown-item>Export PDF</x-dropdown-item>
                    <x-dropdown-item>Export Excel</x-dropdown-item>
                </x-dropdown>
            </div>
            <div class="h-64">
                <canvas id="chartJenisDokumen"></canvas>
            </div>
        </x-glass-card>

        <!-- Dokumen Segera Kadaluarsa -->
        <x-glass-card :hover="false">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-[var(--text-primary)]">Segera Kadaluarsa</h3>
                <a href="{{ route('documents.index', ['status' => 'expiring']) }}" class="text-sm text-primary-500 hover:text-primary-600">
                    Lihat semua
                </a>
            </div>
            <div class="space-y-4">
                @forelse($expiringDocuments ?? [] as $doc)
                    <a 
                        href="{{ route('documents.show', $doc) }}"
                        class="block p-3 rounded-lg border border-[var(--surface-glass-border)] hover:border-[var(--color-primary)] hover:bg-[var(--surface-glass)] transition-all"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-[var(--text-primary)] truncate">{{ $doc->title }}</p>
                                <p class="text-xs text-[var(--text-tertiary)] mt-1">{{ $doc->document_number }}</p>
                            </div>
                            <x-badge :type="$doc->status">
                                {{ $doc->days_until_expiry }} hari
                            </x-badge>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-8 text-[var(--text-tertiary)]">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm">Tidak ada dokumen yang akan kadaluarsa</p>
                    </div>
                @endforelse
            </div>
        </x-glass-card>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('chartJenisDokumen');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($chartLabels ?? ['Perjanjian Kerjasama', 'MoU', 'Kontrak', 'Lainnya']) !!},
                        datasets: [{
                            data: {!! json_encode($chartData ?? [30, 25, 20, 25]) !!},
                            backgroundColor: [
                                '#00A0B0',
                                '#A4C639',
                                '#3B82F6',
                                '#8B5CF6',
                            ],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    color: getComputedStyle(document.documentElement).getPropertyValue('--text-primary').trim()
                                }
                            }
                        },
                        cutout: '60%'
                    }
                });
            }
        });
    </script>
    @endpush
</x-layouts.app>
