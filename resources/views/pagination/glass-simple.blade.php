@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-3">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center gap-2 rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] px-3 py-2 text-sm text-[var(--text-tertiary)] opacity-60 cursor-not-allowed">
                <span aria-hidden="true">&lsaquo;</span>
                <span>{!! __('pagination.previous') !!}</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-2 rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] px-3 py-2 text-sm text-[var(--text-secondary)] transition hover:bg-[var(--surface-glass-elevated)] hover:text-[var(--text-primary)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--focus-ring)]">
                <span aria-hidden="true">&lsaquo;</span>
                <span>{!! __('pagination.previous') !!}</span>
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-2 rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] px-3 py-2 text-sm text-[var(--text-secondary)] transition hover:bg-[var(--surface-glass-elevated)] hover:text-[var(--text-primary)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--focus-ring)]">
                <span>{!! __('pagination.next') !!}</span>
                <span aria-hidden="true">&rsaquo;</span>
            </a>
        @else
            <span class="inline-flex items-center gap-2 rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] px-3 py-2 text-sm text-[var(--text-tertiary)] opacity-60 cursor-not-allowed">
                <span>{!! __('pagination.next') !!}</span>
                <span aria-hidden="true">&rsaquo;</span>
            </span>
        @endif
    </nav>
@endif
