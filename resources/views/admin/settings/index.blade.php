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
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Pengaturan Sistem</h1>
            <p class="text-muted mb-0">Konfigurasi aplikasi</p>
        </div>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        
        <div class="row">
            <div class="col-lg-3 mb-4">
                {{-- Settings Navigation --}}
                <div class="glass-card">
                    <div class="list-group list-group-flush" id="settings-tab" role="tablist">
                        @php $first = true; @endphp
                        @foreach($settings as $group => $groupSettings)
                        <a class="list-group-item list-group-item-action {{ $first ? 'active' : '' }}" 
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
                            <i class="bi {{ $icons[$group] ?? 'bi-sliders' }} me-2"></i>
                            {{ ucfirst($group) }}
                        </a>
                        @php $first = false; @endphp
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="col-lg-9">
                <div class="tab-content">
                    @php $first = true; @endphp
                    @foreach($settings as $group => $groupSettings)
                    <div class="tab-pane fade {{ $first ? 'show active' : '' }}" id="content-{{ $group }}" role="tabpanel">
                        <div class="glass-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    Pengaturan {{ ucfirst($group) }}
                                </h5>
                            </div>
                            <div class="card-body">
                                @foreach($groupSettings as $setting)
                                <div class="mb-4">
                                    <label for="setting_{{ $setting->key }}" class="form-label fw-medium">
                                        {{ $setting->display_name }}
                                    </label>
                                    
                                    @switch($setting->type)
                                        @case('boolean')
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="setting_{{ $setting->key }}" 
                                                       name="settings[{{ $setting->key }}]" value="1"
                                                       {{ $setting->value === 'true' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="setting_{{ $setting->key }}">
                                                    Aktif
                                                </label>
                                            </div>
                                            @break
                                            
                                        @case('integer')
                                        @case('number')
                                            <input type="number" class="form-control" 
                                                   id="setting_{{ $setting->key }}" 
                                                   name="settings[{{ $setting->key }}]" 
                                                   value="{{ $setting->value }}"
                                                   style="max-width: 200px;">
                                            @break
                                            
                                        @case('text')
                                            <textarea class="form-control" 
                                                      id="setting_{{ $setting->key }}" 
                                                      name="settings[{{ $setting->key }}]" 
                                                      rows="3">{{ $setting->value }}</textarea>
                                            @break
                                            
                                        @case('json')
                                        @case('array')
                                            <textarea class="form-control font-monospace" 
                                                      id="setting_{{ $setting->key }}" 
                                                      name="settings[{{ $setting->key }}]" 
                                                      rows="4">{{ is_array($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT) : $setting->value }}</textarea>
                                            <small class="text-muted">Format JSON</small>
                                            @break
                                            
                                        @case('select')
                                            @php
                                                $options = json_decode($setting->options ?? '[]', true) ?? [];
                                            @endphp
                                            <select class="form-select" 
                                                    id="setting_{{ $setting->key }}" 
                                                    name="settings[{{ $setting->key }}]"
                                                    style="max-width: 300px;">
                                                @foreach($options as $optValue => $optLabel)
                                                    <option value="{{ $optValue }}" {{ $setting->value === $optValue ? 'selected' : '' }}>
                                                        {{ $optLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @break
                                            
                                        @default
                                            <input type="text" class="form-control" 
                                                   id="setting_{{ $setting->key }}" 
                                                   name="settings[{{ $setting->key }}]" 
                                                   value="{{ $setting->value }}">
                                    @endswitch
                                    
                                    @if($setting->description)
                                        <small class="text-muted d-block mt-1">{{ $setting->description }}</small>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @php $first = false; @endphp
                    @endforeach
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Simpan Pengaturan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
