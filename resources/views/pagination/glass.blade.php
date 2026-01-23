@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-3">
        <div class="flex flex-1 items-center justify-between gap-3 sm:hidden">
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

            <span class="text-xs text-[var(--text-tertiary)]">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

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
        </div>

        <div class="hidden flex-1 items-center justify-between sm:flex">
            <p class="text-sm text-[var(--text-tertiary)]">
                {!! __('Showing') !!}
                @if ($paginator->firstItem())
                    <span class="font-medium text-[var(--text-primary)]">{{ $paginator->firstItem() }}</span>
                    {!! __('to') !!}
                    <span class="font-medium text-[var(--text-primary)]">{{ $paginator->lastItem() }}</span>
                @else
                    <span class="font-medium text-[var(--text-primary)]">{{ $paginator->count() }}</span>
                @endif
                {!! __('of') !!}
                <span class="font-medium text-[var(--text-primary)]">{{ $paginator->total() }}</span>
                {!! __('results') !!}
            </p>

            <div class="flex items-center gap-1" role="list">
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] text-[var(--text-tertiary)] opacity-60 cursor-not-allowed">
                        <span aria-hidden="true">&lsaquo;</span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] text-[var(--text-secondary)] transition hover:bg-[var(--surface-glass-elevated)] hover:text-[var(--text-primary)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--focus-ring)]">
                        <span aria-hidden="true">&lsaquo;</span>
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="inline-flex h-9 min-w-[36px] items-center justify-center rounded-lg border border-transparent px-2 text-sm text-[var(--text-tertiary)]">
                            {{ $element }}
                        </span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="inline-flex h-9 min-w-[36px] items-center justify-center rounded-lg bg-gradient-to-r from-primary-500 to-lime-500 px-3 text-sm font-semibold text-white shadow-sm">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}" class="inline-flex h-9 min-w-[36px] items-center justify-center rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] px-3 text-sm text-[var(--text-secondary)] transition hover:bg-[var(--surface-glass-elevated)] hover:text-[var(--text-primary)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--focus-ring)]">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] text-[var(--text-secondary)] transition hover:bg-[var(--surface-glass-elevated)] hover:text-[var(--text-primary)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--focus-ring)]">
                        <span aria-hidden="true">&rsaquo;</span>
                    </a>
                @else
                    <span aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] text-[var(--text-tertiary)] opacity-60 cursor-not-allowed">
                        <span aria-hidden="true">&rsaquo;</span>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
