@props([
    'name' => '',
    'id' => null,
    'label' => null,
    'placeholder' => 'Pilih...',
    'options' => [],
    'value' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
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

    <div class="relative">
        <select
            name="{{ $name }}"
            id="{{ $inputId }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge([
                'class' => 'glass-input appearance-none pr-10 cursor-pointer' . ($hasError ? ' border-red-500 focus:border-red-500 focus:ring-red-200' : '') . ($disabled ? ' opacity-50 cursor-not-allowed' : '')
            ]) }}
        >
            <option value="">{{ $placeholder }}</option>
            @foreach($options as $optionValue => $optionLabel)
                <option 
                    value="{{ $optionValue }}" 
                    {{ old($name, $value) == $optionValue ? 'selected' : '' }}
                >
                    {{ $optionLabel }}
                </option>
            @endforeach
            {{ $slot }}
        </select>

        <!-- Dropdown Arrow -->
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <svg class="w-5 h-5 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </div>

    @if($hasError)
        <p class="text-xs text-red-500">{{ $error ?? $errors->first($name) }}</p>
    @endif
</div>
