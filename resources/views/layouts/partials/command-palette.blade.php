<!-- Command Palette (Cmd+K) -->
<div 
    x-data="commandPalette()"
    x-on:open-command-palette.window="toggle()"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[60] overflow-y-auto"
>
    <!-- Backdrop -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="close()"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm"
    ></div>

    <!-- Command Palette Container -->
    <div class="fixed inset-0 flex items-start justify-center pt-[20vh]">
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            @click.stop
            class="w-full max-w-xl mx-4"
        >
            <div class="glass-card-static overflow-hidden">
                <!-- Search Input -->
                <div class="flex items-center gap-3 px-4 py-3 border-b border-[var(--surface-glass-border)]">
                    <svg class="w-5 h-5 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input 
                        x-ref="searchInput"
                        x-model="query"
                        type="text"
                        placeholder="Cari dokumen, perintah, atau navigasi..."
                        class="flex-1 bg-transparent border-none focus:ring-0 text-[var(--text-primary)] placeholder-[var(--text-tertiary)]"
                    >
                    <kbd class="px-2 py-1 text-xs font-medium rounded bg-[var(--surface-glass-border)] text-[var(--text-tertiary)]">ESC</kbd>
                </div>

                <!-- Quick Actions -->
                <div class="p-2 max-h-80 overflow-y-auto custom-scrollbar">
                    <div class="mb-2 px-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-[var(--text-tertiary)]">Aksi Cepat</p>
                    </div>

                    <button 
                        @click="close(); window.location.href='{{ route('documents.create') }}'"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-left hover:bg-[var(--surface-glass)] transition-colors"
                    >
                        <div class="w-8 h-8 rounded-lg bg-primary-500/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Tambah Dokumen Baru</p>
                            <p class="text-xs text-[var(--text-tertiary)]">Buat dokumen hukum baru</p>
                        </div>
                    </button>

                    <button 
                        @click="close(); window.location.href='{{ route('documents.index') }}?status=expiring'"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-left hover:bg-[var(--surface-glass)] transition-colors"
                    >
                        <div class="w-8 h-8 rounded-lg bg-yellow-500/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Dokumen Hampir Kadaluarsa</p>
                            <p class="text-xs text-[var(--text-tertiary)]">Lihat dokumen yang akan berakhir</p>
                        </div>
                    </button>

                    <div class="my-2 border-t border-[var(--surface-glass-border)]"></div>

                    <div class="mb-2 px-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-[var(--text-tertiary)]">Navigasi</p>
                    </div>

                    <button 
                        @click="close(); window.location.href='{{ route('dashboard') }}'"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-left hover:bg-[var(--surface-glass)] transition-colors"
                    >
                        <svg class="w-5 h-5 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span class="text-sm">Dashboard</span>
                    </button>

                    <button 
                        @click="close(); window.location.href='{{ route('documents.index') }}'"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-left hover:bg-[var(--surface-glass)] transition-colors"
                    >
                        <svg class="w-5 h-5 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="text-sm">Semua Dokumen</span>
                    </button>
                </div>

                <!-- Footer -->
                <div class="px-4 py-2 border-t border-[var(--surface-glass-border)] bg-[var(--surface-glass)]">
                    <div class="flex items-center justify-between text-xs text-[var(--text-tertiary)]">
                        <span>Tekan <kbd class="px-1.5 py-0.5 rounded bg-[var(--surface-glass-border)]">↵</kbd> untuk memilih</span>
                        <span>Tekan <kbd class="px-1.5 py-0.5 rounded bg-[var(--surface-glass-border)]">ESC</kbd> untuk menutup</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
