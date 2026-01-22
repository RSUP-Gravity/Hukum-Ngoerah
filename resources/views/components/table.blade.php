@props([
    'striped' => false,
    'hoverable' => true,
])

@php
    $tableClasses = 'glass-table min-w-full' . ($hoverable ? '' : ' no-hover');
@endphp

<div class="glass-card-static overflow-hidden">
    <div class="overflow-x-auto">
        <table {{ $attributes->merge(['class' => $tableClasses]) }}>
            @if(isset($header))
                <thead>
                    <tr>
                        {{ $header }}
                    </tr>
                </thead>
            @endif
            
            <tbody class="{{ $striped ? 'divide-y divide-[var(--surface-glass-border)]' : '' }}">
                {{ $slot }}
            </tbody>

            @if(isset($footer))
                <tfoot>
                    {{ $footer }}
                </tfoot>
            @endif
        </table>
    </div>

    @if(isset($pagination))
        <div class="px-4 py-3 border-t border-[var(--surface-glass-border)]">
            {{ $pagination }}
        </div>
    @endif
</div>
