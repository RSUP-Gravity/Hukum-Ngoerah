@props([
    'sortable' => false,
    'sorted' => null,
    'sortKey' => null,
])

<th {{ $attributes->merge(['class' => 'px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--text-secondary)] border-b border-[var(--surface-glass-border)]']) }}>
    @if($sortable && $sortKey)
        <a 
            href="{{ request()->fullUrlWithQuery(['sort' => $sortKey, 'direction' => $sorted === 'asc' ? 'desc' : 'asc']) }}"
            class="inline-flex items-center gap-1 hover:text-[var(--text-primary)] transition-colors"
        >
            {{ $slot }}
            <span class="inline-flex flex-col">
                <svg class="w-3 h-3 {{ $sorted === 'asc' ? 'text-primary-500' : 'text-[var(--text-tertiary)]' }}" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 12l5-5 5 5H5z"/>
                </svg>
                <svg class="w-3 h-3 -mt-1 {{ $sorted === 'desc' ? 'text-primary-500' : 'text-[var(--text-tertiary)]' }}" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 8l5 5 5-5H5z"/>
                </svg>
            </span>
        </a>
    @else
        {{ $slot }}
    @endif
</th>
