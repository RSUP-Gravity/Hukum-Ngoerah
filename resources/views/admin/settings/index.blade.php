@extends('layouts.app')

@section('title', 'Pengaturan Sistem')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Admin'],
        ['label' => 'Pengaturan']
    ]" />
@endsection

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Pengaturan Sistem</h1>
        <p class="mt-1 text-sm text-[var(--text-secondary)]">Konfigurasi aplikasi</p>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <div class="lg:col-span-3">
                {{-- Settings Navigation --}}
                <x-glass-card :hover="false" class="p-4">
                    <div class="settings-tab space-y-2" id="settings-tab" role="tablist">
                        @php $first = true; @endphp
                        @foreach($settings as $group => $groupSettings)
                        <a class="settings-tab-item {{ $first ? 'active' : '' }}"
                           id="tab-{{ $group }}" data-bs-toggle="list" href="#content-{{ $group }}" role="tab">
                            @php
                                $icons = [
                                    'general' => 'bi-gear',
                                    'security' => 'bi-shield-lock',
                                    'documents' => 'bi-file-earmark-text',
                                    'appearance' => 'bi-palette',
                                    'email' => 'bi-envelope',
                                    'notifications' => 'bi-bell',
                                ];
                            @endphp
                            <i class="bi {{ $icons[$group] ?? 'bi-sliders' }}"></i>
                            {{ ucfirst($group) }}
                        </a>
                        @php $first = false; @endphp
                        @endforeach
                    </div>
                </x-glass-card>
            </div>

            <div class="lg:col-span-9 space-y-6">
                <div class="tab-content">
                    @php $first = true; @endphp
                    @foreach($settings as $group => $groupSettings)
                    <div class="tab-pane fade {{ $first ? 'show active' : '' }}" id="content-{{ $group }}" role="tabpanel">
                        <x-glass-card :hover="false" class="p-6">
                            <div class="mb-4">
                                <h2 class="text-lg font-semibold text-[var(--text-primary)]">Pengaturan {{ ucfirst($group) }}</h2>
                            </div>
                            <div class="space-y-6">
                                @foreach($groupSettings as $setting)
                                <div class="space-y-2">
                                    <label for="setting_{{ $setting->key }}" class="text-sm font-medium text-[var(--text-primary)]">
                                        {{ $setting->display_name }}
                                    </label>

                                    @switch($setting->type)
                                        @case('boolean')
                                            <label class="inline-flex items-center gap-2 text-sm text-[var(--text-secondary)]">
                                                <input class="h-4 w-4 rounded border-[var(--surface-glass-border)] text-primary-500 focus:ring-primary-500" type="checkbox"
                                                       id="setting_{{ $setting->key }}"
                                                       name="settings[{{ $setting->key }}]" value="1"
                                                       {{ $setting->value === 'true' ? 'checked' : '' }}>
                                                Aktif
                                            </label>
                                            @break

                                        @case('integer')
                                        @case('number')
                                            <input type="number" class="glass-input max-w-[200px]"
                                                   id="setting_{{ $setting->key }}"
                                                   name="settings[{{ $setting->key }}]"
                                                   value="{{ $setting->value }}">
                                            @break

                                        @case('text')
                                            <textarea class="glass-input" id="setting_{{ $setting->key }}"
                                                      name="settings[{{ $setting->key }}]" rows="3">{{ $setting->value }}</textarea>
                                            @break

                                        @case('json')
                                        @case('array')
                                            <textarea class="glass-input font-mono" id="setting_{{ $setting->key }}"
                                                      name="settings[{{ $setting->key }}]" rows="4">{{ is_array($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT) : $setting->value }}</textarea>
                                            <p class="text-xs text-[var(--text-tertiary)]">Format JSON</p>
                                            @break

                                        @case('select')
                                            @php
                                                $options = json_decode($setting->options ?? '[]', true) ?? [];
                                            @endphp
                                            <select class="glass-input max-w-[300px]"
                                                    id="setting_{{ $setting->key }}"
                                                    name="settings[{{ $setting->key }}]">
                                                @foreach($options as $optValue => $optLabel)
                                                    <option value="{{ $optValue }}" {{ $setting->value === $optValue ? 'selected' : '' }}>
                                                        {{ $optLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @break

                                        @default
                                            <input type="text" class="glass-input" id="setting_{{ $setting->key }}"
                                                   name="settings[{{ $setting->key }}]" value="{{ $setting->value }}">
                                    @endswitch

                                    @if($setting->description)
                                        <p class="text-xs text-[var(--text-tertiary)]">{{ $setting->description }}</p>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </x-glass-card>
                    </div>
                    @php $first = false; @endphp
                    @endforeach
                </div>

                <div class="flex justify-end">
                    <x-button type="submit">
                        <i class="bi bi-check-lg"></i>
                        Simpan Pengaturan
                    </x-button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

<style>
.settings-tab-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.75rem 1rem;
    border-radius: 12px;
    color: var(--text-secondary);
    border: 1px solid transparent;
    transition: all 0.2s ease;
    background: transparent;
}

.settings-tab-item:hover {
    color: var(--text-primary);
    background: var(--surface-glass);
    border-color: var(--surface-glass-border);
}

.settings-tab-item.active {
    color: var(--text-primary);
    background: var(--surface-glass);
    border-color: var(--surface-glass-border);
    box-shadow: var(--shadow-glass);
}
</style>
