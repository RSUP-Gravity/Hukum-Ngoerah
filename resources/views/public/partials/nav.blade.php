<nav class="sticky top-0 z-40 border-b border-[var(--surface-glass-border)] bg-[var(--surface-glass)]/80 backdrop-blur-xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-4">
        <a href="{{ route('landing') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/Logo-RS-New.png') }}" alt="RS Ngoerah" class="h-9 w-9 rounded-lg bg-white/80 p-1">
            <div class="leading-tight">
                <div class="text-sm font-semibold text-[var(--text-primary)]">RS Ngoerah</div>
                <div class="text-xs text-[var(--text-tertiary)]">Legal Document Center</div>
            </div>
        </a>

        <div class="flex items-center gap-3">
            <a href="{{ route('public.documents') }}"
               class="hidden sm:inline-flex items-center text-sm font-medium {{ request()->routeIs('public.documents') ? 'text-[var(--color-primary)]' : 'text-[var(--text-secondary)]' }} hover:text-[var(--text-primary)] transition-colors">
                Dokumen Publik
            </a>
            <a href="{{ route('login') }}"
               class="hidden sm:inline-flex items-center text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors">
                Masuk
            </a>
            <x-button href="{{ route('public.documents') }}" size="sm">
                Lihat Dokumen Publik
            </x-button>
            <button type="button" onclick="darkMode.toggle()" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-[var(--surface-glass-border)] text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--surface-glass)] transition-colors" aria-label="Toggle theme">
                <i class="bi bi-moon-stars"></i>
            </button>
        </div>
    </div>
</nav>
