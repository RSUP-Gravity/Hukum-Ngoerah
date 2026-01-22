@props([
    'name' => '',
    'id' => null,
    'label' => null,
    'placeholder' => '',
    'value' => '',
    'rows' => 4,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'hint' => null,
])

@php
    $inputId = $id ?? $name;
    $hasError = $error || $errors->has($name);
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-[var(--text-primary)]">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $inputId }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge([
            'class' => 'glass-input resize-y' . ($hasError ? ' border-red-500 focus:border-red-500 focus:ring-red-200' : '') . ($disabled ? ' opacity-50 cursor-not-allowed' : '')
        ]) }}
    >{{ old($name, $value) }}</textarea>

    @if($hint && !$hasError)
        <p class="text-xs text-[var(--text-tertiary)]">{{ $hint }}</p>
    @endif

    @if($hasError)
        <p class="text-xs text-red-500">{{ $error ?? $errors->first($name) }}</p>
    @endif
</div>
