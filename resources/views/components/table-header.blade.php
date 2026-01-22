@props([
    'sortable' => false,
    'sorted' => null,
    'sortKey' => null,
    'sortPriority' => null,
])

@php
    // Parse current sort state from URL
    $currentSorts = array_filter(explode(',', request('sort', '')));
    $currentDirs = array_filter(explode(',', request('dir', '')));
    
    // Find if this column is currently sorted
    $sortIndex = array_search($sortKey, $currentSorts);
    $isSorted = $sortIndex !== false;
    $currentDir = $isSorted && isset($currentDirs[$sortIndex]) ? $currentDirs[$sortIndex] : null;
    $priority = $isSorted ? $sortIndex + 1 : null;
    
    // Build URL for normal click (single sort)
    $singleSortUrl = request()->fullUrlWithQuery([
        'sort' => $sortKey, 
        'dir' => $currentDir === 'asc' ? 'desc' : 'asc'
    ]);
@endphp

<th {{ $attributes->merge(['class' => 'px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--text-secondary)] border-b border-[var(--surface-glass-border)]']) }}>
    @if($sortable && $sortKey)
        <a 
            href="{{ $singleSortUrl }}"
            class="inline-flex items-center gap-1 hover:text-[var(--text-primary)] transition-colors group multi-sort-header"
            data-sort-key="{{ $sortKey }}"
            data-current-dir="{{ $currentDir }}"
            data-is-sorted="{{ $isSorted ? 'true' : 'false' }}"
            onclick="return handleMultiSort(event, '{{ $sortKey }}', '{{ $currentDir }}')"
            title="Klik untuk sort. Shift+klik untuk multi-column sort"
        >
            {{ $slot }}
            <span class="inline-flex items-center">
                @if($priority && count($currentSorts) > 1)
                    <span class="text-[10px] font-bold text-primary-500 mr-0.5 bg-primary-500/10 rounded-full w-4 h-4 flex items-center justify-center">
                        {{ $priority }}
                    </span>
                @endif
                <span class="inline-flex flex-col">
                    <svg class="w-3 h-3 {{ $currentDir === 'asc' ? 'text-primary-500' : 'text-[var(--text-tertiary)] opacity-50 group-hover:opacity-100' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 12l5-5 5 5H5z"/>
                    </svg>
                    <svg class="w-3 h-3 -mt-1 {{ $currentDir === 'desc' ? 'text-primary-500' : 'text-[var(--text-tertiary)] opacity-50 group-hover:opacity-100' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 8l5 5 5-5H5z"/>
                    </svg>
                </span>
            </span>
        </a>
    @else
        {{ $slot }}
    @endif
</th>
