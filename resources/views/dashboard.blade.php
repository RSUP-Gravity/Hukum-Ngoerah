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

    {{-- Login Notification Modal for Critical Documents --}}
    @if($showLoginNotification && $criticalDocuments->count() > 0)
    <div x-data="{ open: true }" x-show="open" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Backdrop --}}
            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="open = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Content --}}
            <div x-show="open" x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative inline-block align-bottom glass-card rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                
                {{-- Header with Alert Icon --}}
                <div class="bg-gradient-to-r from-red-500/10 to-orange-500/10 dark:from-red-500/20 dark:to-orange-500/20 px-6 py-4 border-b border-red-200/50 dark:border-red-800/50">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/50 flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-[var(--text-primary)]" id="modal-title">
                                Perhatian: Dokumen Kritis
                            </h3>
                            <p class="text-sm text-[var(--text-secondary)]">
                                {{ $criticalDocuments->count() }} dokumen memerlukan perhatian Anda
                            </p>
                        </div>
                    </div>
                </div>
                
                {{-- Document List --}}
                <div class="px-6 py-4 max-h-[50vh] overflow-y-auto">
                    <div class="space-y-3">
                        @foreach($criticalDocuments as $doc)
                        @php
                            $isExpired = $doc->expiry_date && $doc->expiry_date->isPast();
                            $daysLeft = $doc->expiry_date ? now()->diffInDays($doc->expiry_date, false) : null;
                        @endphp
                        <a href="{{ route('documents.show', $doc) }}" 
                           class="block p-3 rounded-lg border transition-all hover:shadow-md
                                  {{ $isExpired 
                                     ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800/50 hover:bg-red-100 dark:hover:bg-red-900/30' 
                                     : 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800/50 hover:bg-orange-100 dark:hover:bg-orange-900/30' 
                                  }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-[var(--text-primary)] truncate">
                                        {{ $doc->title }}
                                    </p>
                                    <p class="text-xs text-[var(--text-secondary)] mt-1">
                                        {{ $doc->document_number }} · {{ $doc->documentType?->name }}
                                    </p>
                                </div>
                                <div class="flex-shrink-0 text-right">
                                    @if($isExpired)
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            Kadaluarsa
                                        </span>
                                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">
                                            {{ abs($daysLeft) }} hari lalu
                                        </p>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            Segera
                                        </span>
                                        <p class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                                            {{ $daysLeft }} hari lagi
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                
                {{-- Footer Actions --}}
                <div class="bg-[var(--surface-elevated)] px-6 py-4 border-t border-[var(--surface-glass-border)] flex flex-col sm:flex-row gap-3 sm:justify-between">
                    <a href="{{ route('documents.index', ['status' => 'expiring']) }}" 
                       class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg text-white bg-gradient-to-r from-primary-500 to-lime-500 hover:from-primary-600 hover:to-lime-600 transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Lihat Semua Dokumen Kritis
                    </a>
                    <button @click="open = false" type="button"
                            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg text-[var(--text-secondary)] bg-[var(--surface-glass)] hover:bg-[var(--surface-elevated)] border border-[var(--surface-glass-border)] transition-all">
                        Tutup & Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions Widget -->
    <div class="mb-8">
        <x-glass-card :hover="false">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-[var(--text-primary)]">
                    <svg class="w-5 h-5 inline-block mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Aksi Cepat
                </h3>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- Add New Document -->
                <a href="{{ route('documents.create') }}" 
                   class="flex flex-col items-center p-4 rounded-xl border border-[var(--surface-glass-border)] hover:border-[var(--color-primary)] hover:bg-[var(--surface-glass)] transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-primary-500/10 flex items-center justify-center mb-2 group-hover:bg-primary-500/20 transition-colors">
                        <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-[var(--text-primary)] text-center">Tambah Dokumen</span>
                </a>

                <!-- View All Documents -->
                <a href="{{ route('documents.index') }}" 
                   class="flex flex-col items-center p-4 rounded-xl border border-[var(--surface-glass-border)] hover:border-[var(--color-primary)] hover:bg-[var(--surface-glass)] transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center mb-2 group-hover:bg-blue-500/20 transition-colors">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-[var(--text-primary)] text-center">Semua Dokumen</span>
                </a>

                <!-- Export Report -->
                <a href="{{ route('documents.export') }}" 
                   class="flex flex-col items-center p-4 rounded-xl border border-[var(--surface-glass-border)] hover:border-[var(--color-primary)] hover:bg-[var(--surface-glass)] transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center mb-2 group-hover:bg-green-500/20 transition-colors">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-[var(--text-primary)] text-center">Export Laporan</span>
                </a>

                <!-- Expiring Documents -->
                <a href="{{ route('documents.index', ['status' => 'expiring']) }}" 
                   class="flex flex-col items-center p-4 rounded-xl border border-[var(--surface-glass-border)] hover:border-[var(--color-primary)] hover:bg-[var(--surface-glass)] transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-yellow-500/10 flex items-center justify-center mb-2 group-hover:bg-yellow-500/20 transition-colors">
                        <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-[var(--text-primary)] text-center">Akan Kadaluarsa</span>
                </a>

                <!-- Expired Documents -->
                <a href="{{ route('documents.index', ['status' => 'expired']) }}" 
                   class="flex flex-col items-center p-4 rounded-xl border border-[var(--surface-glass-border)] hover:border-[var(--color-primary)] hover:bg-[var(--surface-glass)] transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-red-500/10 flex items-center justify-center mb-2 group-hover:bg-red-500/20 transition-colors">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-[var(--text-primary)] text-center">Kadaluarsa</span>
                </a>

                <!-- Search Documents -->
                <button 
                   type="button"
                   x-data
                   @click="$dispatch('open-command-palette')"
                   class="flex flex-col items-center p-4 rounded-xl border border-[var(--surface-glass-border)] hover:border-[var(--color-primary)] hover:bg-[var(--surface-glass)] transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center mb-2 group-hover:bg-purple-500/20 transition-colors">
                        <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-[var(--text-primary)] text-center">Cari Dokumen</span>
                </button>
            </div>
        </x-glass-card>
    </div>

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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Chart: Dokumen per Jenis -->
        <x-glass-card :hover="false">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-[var(--text-primary)]">Per Jenis Dokumen</h3>
            </div>
            <div class="h-56">
                <canvas id="chartJenisDokumen"></canvas>
            </div>
        </x-glass-card>

        <!-- Chart: Dokumen per Tipe -->
        <x-glass-card :hover="false">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-[var(--text-primary)]">Per Tipe Dokumen</h3>
            </div>
            <div class="h-56">
                <canvas id="chartTipeDokumen"></canvas>
            </div>
        </x-glass-card>

        <!-- Chart: Dokumen per Direktorat -->
        <x-glass-card :hover="false">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-[var(--text-primary)]">Per Direktorat</h3>
            </div>
            <div class="h-56">
                <canvas id="chartDirektorat"></canvas>
            </div>
        </x-glass-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Chart: Trend Upload -->
        <x-glass-card :hover="false" class="lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-[var(--text-primary)]">Trend Upload Dokumen (12 Bulan)</h3>
            </div>
            <div class="h-64">
                <canvas id="chartTrend"></canvas>
            </div>
        </x-glass-card>

        <!-- Chart: Timeline Kadaluarsa -->
        <x-glass-card :hover="false">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-[var(--text-primary)]">Jadwal Kadaluarsa</h3>
            </div>
            <div class="h-64">
                <canvas id="chartExpiry"></canvas>
            </div>
        </x-glass-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Dokumen Segera Kadaluarsa -->
        <x-glass-card :hover="false">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-[var(--text-primary)]">Segera Kadaluarsa</h3>
                <a href="{{ route('documents.index', ['status' => 'expiring']) }}" class="text-sm text-primary-500 hover:text-primary-600">
                    Lihat semua
                </a>
            </div>
            <div class="space-y-4 max-h-80 overflow-y-auto custom-scrollbar">
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

        <!-- Dokumen Terbaru -->
        <x-glass-card :hover="false">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-[var(--text-primary)]">Dokumen Terbaru</h3>
                <a href="{{ route('documents.index') }}" class="text-sm text-primary-500 hover:text-primary-600">
                    Lihat semua
                </a>
            </div>
            <div class="space-y-4 max-h-80 overflow-y-auto custom-scrollbar">
                @forelse($recentDocuments ?? [] as $doc)
                    <a 
                        href="{{ route('documents.show', $doc) }}"
                        class="block p-3 rounded-lg border border-[var(--surface-glass-border)] hover:border-[var(--color-primary)] hover:bg-[var(--surface-glass)] transition-all"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-[var(--text-primary)] truncate">{{ $doc->title }}</p>
                                <p class="text-xs text-[var(--text-tertiary)] mt-1">{{ $doc->document_number }}</p>
                            </div>
                            <span class="text-xs text-[var(--text-tertiary)]">{{ $doc->created_at->diffForHumans() }}</span>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-8 text-[var(--text-tertiary)]">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-sm">Belum ada dokumen</p>
                    </div>
                @endforelse
            </div>
        </x-glass-card>
    </div>

    <!-- Activity Timeline Section -->
    <div class="mb-8">
        <x-glass-card :hover="false">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500/20 to-lime-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-[var(--text-primary)]">Aktivitas Terbaru</h3>
                        <p class="text-sm text-[var(--text-tertiary)]">Timeline aktivitas dokumen</p>
                    </div>
                </div>
            </div>
            
            <div class="relative">
                <!-- Timeline Line -->
                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gradient-to-b from-primary-500/30 via-lime-500/20 to-transparent"></div>
                
                <!-- Timeline Items -->
                <div class="space-y-4 max-h-96 overflow-y-auto custom-scrollbar pr-2">
                    @forelse($recentActivities ?? [] as $activity)
                    @php
                        $actionColors = [
                            'created' => 'bg-green-500',
                            'updated' => 'bg-blue-500',
                            'version_uploaded' => 'bg-purple-500',
                            'submitted_for_review' => 'bg-yellow-500',
                            'reviewed' => 'bg-cyan-500',
                            'submitted_for_approval' => 'bg-orange-500',
                            'approved' => 'bg-emerald-500',
                            'rejected' => 'bg-red-500',
                            'published' => 'bg-primary-500',
                            'unpublished' => 'bg-gray-500',
                            'archived' => 'bg-slate-500',
                            'restored' => 'bg-teal-500',
                            'deleted' => 'bg-red-600',
                            'downloaded' => 'bg-indigo-500',
                            'printed' => 'bg-violet-500',
                            'shared' => 'bg-pink-500',
                            'locked' => 'bg-amber-500',
                            'unlocked' => 'bg-lime-500',
                            'comment_added' => 'bg-sky-500',
                            'status_changed' => 'bg-fuchsia-500',
                            'version_restored' => 'bg-teal-600',
                        ];
                        $actionIcons = [
                            'created' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>',
                            'updated' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>',
                            'version_uploaded' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>',
                            'approved' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
                            'rejected' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
                            'published' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
                            'downloaded' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>',
                            'deleted' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>',
                            'restored' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>',
                        ];
                        $dotColor = $actionColors[$activity->action] ?? 'bg-gray-400';
                        $iconPath = $actionIcons[$activity->action] ?? $actionIcons['updated'];
                    @endphp
                    <div class="relative pl-10 pb-4 group">
                        <!-- Timeline Dot -->
                        <div class="absolute left-2 top-1 w-4 h-4 rounded-full {{ $dotColor }} ring-4 ring-[var(--surface-glass)] shadow-lg transform group-hover:scale-110 transition-transform"></div>
                        
                        <!-- Activity Card -->
                        <div class="p-3 rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] hover:bg-[var(--surface-elevated)] transition-all">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-8 h-8 rounded-lg {{ $dotColor }}/10 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 {{ str_replace('bg-', 'text-', $dotColor) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            {!! $iconPath !!}
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-[var(--text-primary)]">
                                            {{ $activity->action_label }}
                                        </p>
                                        @if($activity->document)
                                        <a href="{{ route('documents.show', $activity->document) }}" class="text-xs text-primary-500 hover:text-primary-600 truncate block">
                                            {{ Str::limit($activity->document->title, 40) }}
                                        </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-xs text-[var(--text-tertiary)]">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </p>
                                    @if($activity->performer)
                                    <p class="text-xs text-[var(--text-tertiary)]">
                                        oleh {{ Str::limit($activity->performer->name, 15) }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                            @if($activity->notes)
                            <p class="text-xs text-[var(--text-tertiary)] mt-2 pl-10 italic">
                                "{{ Str::limit($activity->notes, 80) }}"
                            </p>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-[var(--text-tertiary)]">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm">Belum ada aktivitas</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </x-glass-card>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Chart instances storage for theme updates
            const chartInstances = {};
            
            // Function to get current theme
            function isDarkMode() {
                return document.documentElement.classList.contains('dark') || 
                       (localStorage.getItem('darkMode') === 'true');
            }
            
            // Function to get theme colors
            function getThemeColors() {
                const dark = isDarkMode();
                return {
                    textColor: dark ? '#E2E8F0' : '#1E293B',
                    textSecondary: dark ? '#94A3B8' : '#64748B',
                    gridColor: dark ? 'rgba(148, 163, 184, 0.15)' : 'rgba(148, 163, 184, 0.2)',
                    tooltipBg: dark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                    tooltipText: dark ? '#F1F5F9' : '#0F172A',
                    tooltipBorder: dark ? 'rgba(0, 196, 214, 0.4)' : 'rgba(0, 160, 176, 0.3)',
                    chartColors: dark 
                        ? ['#22D3EE', '#BEF264', '#60A5FA', '#A78BFA', '#F472B6', 
                           '#FCD34D', '#34D399', '#FB7185', '#818CF8', '#2DD4BF']
                        : ['#00A0B0', '#A4C639', '#3B82F6', '#8B5CF6', '#EC4899', 
                           '#F59E0B', '#10B981', '#EF4444', '#6366F1', '#14B8A6'],
                    primaryColor: dark ? '#22D3EE' : '#00A0B0',
                    primaryBgColor: dark ? 'rgba(34, 211, 238, 0.25)' : 'rgba(0, 160, 176, 0.15)',
                    cardBg: dark ? 'rgba(30, 41, 59, 0.8)' : 'rgba(255, 255, 255, 0.8)',
                    severityColors: {
                        high: dark ? '#FB7185' : '#EF4444',
                        medium: dark ? '#FCD34D' : '#F59E0B',
                        low: dark ? '#34D399' : '#10B981'
                    }
                };
            }
            
            // Apply theme to Chart.js defaults
            function applyChartTheme() {
                const colors = getThemeColors();
                Chart.defaults.color = colors.textColor;
                Chart.defaults.borderColor = colors.gridColor;
                Chart.defaults.plugins.tooltip.backgroundColor = colors.tooltipBg;
                Chart.defaults.plugins.tooltip.titleColor = colors.tooltipText;
                Chart.defaults.plugins.tooltip.bodyColor = colors.tooltipText;
                Chart.defaults.plugins.tooltip.borderColor = colors.tooltipBorder;
                Chart.defaults.plugins.tooltip.borderWidth = 1;
                Chart.defaults.plugins.legend.labels.color = colors.textColor;
                return colors;
            }
            
            // Get current theme colors and apply defaults
            let themeColors = applyChartTheme();

            // Chart: Jenis Dokumen (Doughnut)
            const ctxJenis = document.getElementById('chartJenisDokumen');
            if (ctxJenis) {
                const byTypeData = @json($chartData['by_type'] ?? []);
                chartInstances.jenis = new Chart(ctxJenis, {
                    type: 'doughnut',
                    data: {
                        labels: byTypeData.map(item => item.name),
                        datasets: [{
                            data: byTypeData.map(item => item.total),
                            backgroundColor: themeColors.chartColors.slice(0, byTypeData.length),
                            borderWidth: isDarkMode() ? 2 : 1,
                            borderColor: isDarkMode() ? 'rgba(15, 23, 42, 0.9)' : 'rgba(255, 255, 255, 0.9)',
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        onClick: (event, elements) => {
                            if (elements.length > 0) {
                                const index = elements[0].index;
                                const typeId = byTypeData[index]?.id;
                                if (typeId) {
                                    window.location.href = `{{ route('documents.index') }}?type_id=${typeId}`;
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: { 
                                    padding: 12, 
                                    usePointStyle: true, 
                                    pointStyle: 'circle',
                                    font: { size: 11, weight: '500' },
                                    color: themeColors.textColor
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    afterLabel: () => 'Klik untuk filter'
                                },
                                padding: 12,
                                boxPadding: 6
                            }
                        },
                        cutout: '55%',
                        animation: {
                            animateRotate: true,
                            animateScale: true
                        }
                    }
                });
            }

            // Chart: Tipe Dokumen (Doughnut)
            const ctxTipe = document.getElementById('chartTipeDokumen');
            if (ctxTipe) {
                const byCategoryData = @json($chartData['by_category'] ?? []);
                chartInstances.tipe = new Chart(ctxTipe, {
                    type: 'doughnut',
                    data: {
                        labels: byCategoryData.map(item => item.name),
                        datasets: [{
                            data: byCategoryData.map(item => item.total),
                            backgroundColor: themeColors.chartColors.slice(0, byCategoryData.length).reverse(),
                            borderWidth: isDarkMode() ? 2 : 1,
                            borderColor: isDarkMode() ? 'rgba(15, 23, 42, 0.9)' : 'rgba(255, 255, 255, 0.9)',
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        onClick: (event, elements) => {
                            if (elements.length > 0) {
                                const index = elements[0].index;
                                const categoryId = byCategoryData[index]?.id;
                                if (categoryId) {
                                    window.location.href = `{{ route('documents.index') }}?category_id=${categoryId}`;
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: { 
                                    padding: 12, 
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    font: { size: 11, weight: '500' },
                                    color: themeColors.textColor
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    afterLabel: () => 'Klik untuk filter'
                                },
                                padding: 12,
                                boxPadding: 6
                            }
                        },
                        cutout: '55%',
                        animation: {
                            animateRotate: true,
                            animateScale: true
                        }
                    }
                });
            }

            // Chart: Per Direktorat (Bar Horizontal)
            const ctxDir = document.getElementById('chartDirektorat');
            if (ctxDir) {
                const byDirectorateData = @json($chartData['by_directorate'] ?? []);
                chartInstances.direktorat = new Chart(ctxDir, {
                    type: 'bar',
                    data: {
                        labels: byDirectorateData.map(item => item.name.length > 15 ? item.name.substring(0, 15) + '...' : item.name),
                        datasets: [{
                            data: byDirectorateData.map(item => item.total),
                            backgroundColor: themeColors.primaryColor,
                            hoverBackgroundColor: isDarkMode() ? '#67E8F9' : '#00B7C8',
                            borderRadius: 6,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        onClick: (event, elements) => {
                            if (elements.length > 0) {
                                const index = elements[0].index;
                                const directorateId = byDirectorateData[index]?.id;
                                if (directorateId) {
                                    window.location.href = `{{ route('documents.index') }}?directorate_id=${directorateId}`;
                                }
                            }
                        },
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    afterLabel: () => 'Klik untuk filter'
                                },
                                padding: 12,
                                boxPadding: 6
                            }
                        },
                        scales: {
                            x: { 
                                grid: { 
                                    color: themeColors.gridColor,
                                    drawBorder: false
                                },
                                ticks: { 
                                    color: themeColors.textSecondary,
                                    font: { size: 11 }
                                },
                                border: { display: false }
                            },
                            y: { 
                                grid: { display: false },
                                ticks: { 
                                    color: themeColors.textColor,
                                    font: { size: 11, weight: '500' }
                                },
                                border: { display: false }
                            }
                        }
                    }
                });
            }

            // Chart: Trend Upload (Line)
            const ctxTrend = document.getElementById('chartTrend');
            if (ctxTrend) {
                const perMonthData = @json($chartData['per_month'] ?? []);
                chartInstances.trend = new Chart(ctxTrend, {
                    type: 'line',
                    data: {
                        labels: Object.keys(perMonthData),
                        datasets: [{
                            label: 'Dokumen Diupload',
                            data: Object.values(perMonthData),
                            borderColor: themeColors.primaryColor,
                            backgroundColor: themeColors.primaryBgColor,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: themeColors.primaryColor,
                            pointBorderColor: isDarkMode() ? '#0F172A' : '#FFFFFF',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            pointHoverBackgroundColor: isDarkMode() ? '#67E8F9' : '#00B7C8',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                padding: 12,
                                boxPadding: 6
                            }
                        },
                        scales: {
                            x: { 
                                grid: { 
                                    color: themeColors.gridColor,
                                    drawBorder: false
                                },
                                ticks: { 
                                    color: themeColors.textSecondary,
                                    font: { size: 11 }
                                },
                                border: { display: false }
                            },
                            y: { 
                                grid: { 
                                    color: themeColors.gridColor,
                                    drawBorder: false
                                }, 
                                beginAtZero: true,
                                ticks: { 
                                    color: themeColors.textSecondary,
                                    font: { size: 11 }
                                },
                                border: { display: false }
                            }
                        }
                    }
                });
            }

            // Chart: Expiry Timeline (Bar)
            const ctxExpiry = document.getElementById('chartExpiry');
            if (ctxExpiry) {
                const expiryData = @json($chartData['expiry_timeline'] ?? []);
                
                chartInstances.expiry = new Chart(ctxExpiry, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(expiryData),
                        datasets: [{
                            label: 'Dokumen Kadaluarsa',
                            data: Object.values(expiryData),
                            backgroundColor: (ctx) => {
                                const value = ctx.parsed?.y || 0;
                                if (value > 5) return themeColors.severityColors.high;
                                if (value > 2) return themeColors.severityColors.medium;
                                return themeColors.severityColors.low;
                            },
                            borderRadius: 6,
                            borderSkipped: false,
                            hoverBackgroundColor: (ctx) => {
                                const value = ctx.parsed?.y || 0;
                                if (value > 5) return isDarkMode() ? '#FDA4AF' : '#F87171';
                                if (value > 2) return isDarkMode() ? '#FDE68A' : '#FBBF24';
                                return isDarkMode() ? '#6EE7B7' : '#34D399';
                            }
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                padding: 12,
                                boxPadding: 6
                            }
                        },
                        scales: {
                            x: { 
                                grid: { display: false },
                                ticks: { 
                                    color: themeColors.textSecondary,
                                    font: { size: 11 }
                                },
                                border: { display: false }
                            },
                            y: { 
                                grid: { 
                                    color: themeColors.gridColor,
                                    drawBorder: false
                                }, 
                                beginAtZero: true,
                                ticks: { 
                                    color: themeColors.textSecondary,
                                    font: { size: 11 }
                                },
                                border: { display: false }
                            }
                        }
                    }
                });
            }
            
            // Function to update all charts when theme changes
            function updateChartsTheme() {
                themeColors = applyChartTheme();
                
                // Update each chart
                Object.values(chartInstances).forEach(chart => {
                    if (chart) {
                        chart.options.scales?.x?.ticks && (chart.options.scales.x.ticks.color = themeColors.textSecondary);
                        chart.options.scales?.y?.ticks && (chart.options.scales.y.ticks.color = themeColors.textSecondary);
                        chart.options.scales?.x?.grid && (chart.options.scales.x.grid.color = themeColors.gridColor);
                        chart.options.scales?.y?.grid && (chart.options.scales.y.grid.color = themeColors.gridColor);
                        chart.options.plugins?.legend?.labels && (chart.options.plugins.legend.labels.color = themeColors.textColor);
                        chart.update('none');
                    }
                });
            }
            
            // Watch for dark mode changes using MutationObserver
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        // Slight delay to allow CSS transitions
                        setTimeout(updateChartsTheme, 100);
                    }
                });
            });
            
            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class']
            });
            
            // Listen for system dark mode preference changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                // Only update if not manually set
                if (localStorage.getItem('darkMode') === null) {
                    setTimeout(updateChartsTheme, 100);
                }
            });
            
            // Listen for storage changes (when dark mode is toggled in another tab)
            window.addEventListener('storage', (e) => {
                if (e.key === 'darkMode') {
                    setTimeout(updateChartsTheme, 100);
                }
            });
        });
    </script>
    @endpush
</x-layouts.app>
