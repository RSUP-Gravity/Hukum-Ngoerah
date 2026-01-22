@props([
    'type' => 'text',
    'name' => '',
    'id' => null,
    'label' => null,
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'hint' => null,
])

@php
    $inputId = $id ?? $name;
    $hasError = $error || $errors->has($name);
    $describedBy = [];
    if ($hasError) {
        $describedBy[] = $inputId . '-error';
    } elseif ($hint) {
        $describedBy[] = $inputId . '-hint';
    }
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-[var(--text-primary)]">
            {{ $label }}
            @if($required)
                <span class="text-red-500" aria-hidden="true">*</span>
                <span class="sr-only">(required)</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $inputId }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        @if($hasError) aria-invalid="true" @endif
        @if(count($describedBy) > 0) aria-describedby="{{ implode(' ', $describedBy) }}" @endif
        {{ $attributes->merge([
            'class' => 'glass-input' . ($hasError ? ' border-red-500 focus:border-red-500 focus:ring-red-200' : '') . ($disabled ? ' opacity-50 cursor-not-allowed' : '')
        ]) }}
    >

    @if($hint && !$hasError)
        <p id="{{ $inputId }}-hint" class="text-xs text-[var(--text-tertiary)]">{{ $hint }}</p>
    @endif

    @if($hasError)
        <p id="{{ $inputId }}-error" class="text-xs text-red-500" role="alert">{{ $error ?? $errors->first($name) }}</p>
    @endif
</div>
