@props([
    'items' => []
])

<nav {{ $attributes->merge(['class' => 'flex items-center text-sm']) }} aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-2">
        {{-- Home Link --}}
        <li class="inline-flex items-center">
            <a 
                href="{{ route('dashboard') }}" 
                class="inline-flex items-center gap-1 text-[var(--text-secondary)] hover:text-primary-500 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="hidden sm:inline">Beranda</span>
            </a>
        </li>
        
        {{-- Dynamic Items --}}
        @foreach($items as $item)
            <li>
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    @if(isset($item['url']) && $item['url'])
                        <a 
                            href="{{ $item['url'] }}" 
                            class="ml-1 md:ml-2 text-[var(--text-secondary)] hover:text-primary-500 transition-colors"
                        >
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="ml-1 md:ml-2 text-[var(--text-primary)] font-medium">
                            {{ $item['label'] }}
                        </span>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</nav>
