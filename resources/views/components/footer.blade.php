@props([
    'version' => '1.0.0'
])

<footer {{ $attributes->merge(['class' => 'py-4 px-6 border-t border-[var(--surface-glass-border)] bg-[var(--surface-glass)]']) }}>
    <div class="flex flex-col sm:flex-row justify-between items-center gap-2 text-sm text-[var(--text-secondary)]">
        <div class="flex items-center gap-2">
            <span>&copy; {{ date('Y') }} RSUP Prof. Dr. I.G.N.G. Ngoerah</span>
            <span class="hidden sm:inline">•</span>
            <span class="hidden sm:inline">Sistem Manajemen Dokumen Hukum</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-xs px-2 py-0.5 rounded-full bg-primary-500/10 text-primary-500 font-mono">
                v{{ $version }}
            </span>
            <span class="text-xs text-[var(--text-tertiary)]">
                Dibangun dengan ❤️ oleh Tim IT RS Ngoerah
            </span>
        </div>
    </div>
</footer>
