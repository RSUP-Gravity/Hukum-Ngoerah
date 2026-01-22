@extends('layouts.app')

@section('title', 'Daftar Dokumen')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Dokumen']
    ]" />
@endsection

@section('content')
<div class="container-fluid" x-data="documentsIndex()" x-init="$watch('viewMode', value => localStorage.setItem('documentsViewMode', value))">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Dokumen</h1>
            <p class="text-muted mb-0">Kelola dokumen hukum organisasi</p>
        </div>
        <div class="d-flex gap-2">
            {{-- View Toggle --}}
            <div class="btn-group" role="group" aria-label="View toggle">
                <button type="button" 
                        class="btn btn-outline-secondary"
                        :class="{ 'active': viewMode === 'table' }"
                        @click="viewMode = 'table'"
                        title="Tampilan Tabel">
                    <i class="bi bi-list-ul"></i>
                </button>
                <button type="button" 
                        class="btn btn-outline-secondary"
                        :class="{ 'active': viewMode === 'card' }"
                        @click="viewMode = 'card'"
                        title="Tampilan Kartu">
                    <i class="bi bi-grid-3x3-gap"></i>
                </button>
            </div>
            @can('documents.create')
            <a href="{{ route('documents.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Tambah Dokumen
            </a>
            @endcan
        </div>
    </div>

    {{-- Bulk Actions Toolbar --}}
    <div x-show="selectedIds.length > 0" x-cloak x-transition
         class="glass-card mb-4 border-primary">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary fs-6" x-text="selectedIds.length"></span>
                    <span class="text-muted">dokumen dipilih</span>
                    <button type="button" class="btn btn-sm btn-link text-muted p-0 ms-2" @click="clearSelection()">
                        Batalkan pilihan
                    </button>
                </div>
                <div class="d-flex gap-2">
                    @can('documents.view')
                    <button type="button" class="btn btn-sm btn-outline-success" @click="bulkExport('excel')">
                        <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" @click="bulkExport('pdf')">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
                    </button>
                    @endcan
                    @can('documents.edit')
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="bulkArchive()" 
                            :disabled="!canArchive">
                        <i class="bi bi-archive me-1"></i>Arsipkan
                    </button>
                    @endcan
                    @can('documents.delete')
                    <button type="button" class="btn btn-sm btn-outline-danger" @click="confirmBulkDelete()">
                        <i class="bi bi-trash me-1"></i>Hapus
                    </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="glass-card mb-4">
        <div class="card-body">
            <form action="{{ route('documents.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Pencarian</label>
                        <div class="position-relative" x-data="searchAutocomplete()">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" name="search" 
                                       x-model="query"
                                       @input.debounce.300ms="fetchSuggestions()"
                                       @focus="showDropdown = suggestions.length > 0"
                                       @keydown.escape="showDropdown = false"
                                       @keydown.arrow-down.prevent="navigateSuggestion(1)"
                                       @keydown.arrow-up.prevent="navigateSuggestion(-1)"
                                       @keydown.enter="selectSuggestion()"
                                       value="{{ request('search') }}" 
                                       placeholder="Cari judul, nomor, atau deskripsi..."
                                       autocomplete="off">
                            </div>
                            
                            {{-- Autocomplete Dropdown --}}
                            <div x-show="showDropdown && suggestions.length > 0" 
                                 x-cloak
                                 @click.outside="showDropdown = false"
                                 class="position-absolute w-100 mt-1 bg-white dark:bg-slate-800 rounded-3 shadow-lg border z-50"
                                 style="max-height: 350px; overflow-y: auto;">
                                <template x-for="(suggestion, index) in suggestions" :key="suggestion.id">
                                    <a :href="suggestion.url" 
                                       class="d-block px-3 py-2 text-decoration-none border-bottom"
                                       :class="{ 'bg-primary-subtle': selectedIndex === index }"
                                       @mouseenter="selectedIndex = index">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-file-earmark-text text-primary"></i>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="fw-medium text-truncate text-body" x-text="suggestion.title"></div>
                                                <div class="small text-muted d-flex gap-2">
                                                    <span x-text="suggestion.document_number"></span>
                                                    <span>•</span>
                                                    <span x-text="suggestion.type"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </template>
                                <div class="px-3 py-2 text-center small text-muted bg-light">
                                    <i class="bi bi-keyboard me-1"></i>
                                    Gunakan ↑↓ untuk navigasi, Enter untuk memilih
                                </div>
                            </div>
                            
                            {{-- Loading indicator --}}
                            <div x-show="isLoading" class="position-absolute end-0 top-50 translate-middle-y me-3" style="right: 40px;">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Jenis Dokumen</label>
                        <select name="type_id" class="form-select">
                            <option value="">Semua Jenis</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" {{ request('type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Unit</label>
                        <select name="unit_id" class="form-select">
                            <option value="">Semua Unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
                        {{-- Filter Presets Dropdown --}}
                        <div class="dropdown" x-data="filterPresets()">
                            <button class="btn btn-outline-info dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Filter Presets">
                                <i class="bi bi-bookmark"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" style="min-width: 250px;">
                                <li class="dropdown-header">
                                    <i class="bi bi-bookmark-star me-1"></i>Preset Filter
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                
                                {{-- Save Current Filter --}}
                                <li class="px-3 py-2">
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" placeholder="Nama preset..." x-model="newPresetName" @keydown.enter.prevent="savePreset()">
                                        <button class="btn btn-primary" type="button" @click="savePreset()" :disabled="!newPresetName.trim()">
                                            <i class="bi bi-save"></i>
                                        </button>
                                    </div>
                                </li>
                                
                                <li><hr class="dropdown-divider"></li>
                                
                                {{-- Saved Presets --}}
                                <template x-if="presets.length === 0">
                                    <li class="px-3 py-2 text-muted small text-center">
                                        Belum ada preset tersimpan
                                    </li>
                                </template>
                                <template x-for="(preset, index) in presets" :key="index">
                                    <li>
                                        <a class="dropdown-item d-flex justify-content-between align-items-center" href="#" @click.prevent="applyPreset(preset)">
                                            <span>
                                                <i class="bi bi-bookmark-fill me-2 text-primary"></i>
                                                <span x-text="preset.name"></span>
                                            </span>
                                            <button type="button" class="btn btn-sm btn-link text-danger p-0" @click.stop="deletePreset(index)" title="Hapus preset">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </a>
                                    </li>
                                </template>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Export">
                                <i class="bi bi-download"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('documents.export', request()->query()) }}">
                                        <i class="bi bi-file-earmark-excel me-2 text-success"></i>Export ke Excel
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('documents.export-pdf', request()->query()) }}">
                                        <i class="bi bi-file-earmark-pdf me-2 text-danger"></i>Export ke PDF
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                {{-- Advanced Filters Toggle --}}
                <div class="mt-3">
                    <a class="text-decoration-none" data-bs-toggle="collapse" href="#advancedFilters" role="button">
                        <i class="bi bi-sliders me-1"></i>Filter Lanjutan
                    </a>
                    
                    <div class="collapse {{ request()->hasAny(['date_from', 'date_to', 'category_id', 'expired', 'expiring_days']) ? 'show' : '' }}" id="advancedFilters">
                        <div class="row g-3 mt-2">
                            <div class="col-md-2">
                                <label class="form-label">Kategori</label>
                                <select name="category_id" class="form-select">
                                    <option value="">Semua Kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">Status Kedaluwarsa</label>
                                <select name="expiring_days" class="form-select">
                                    <option value="">Semua</option>
                                    <option value="30" {{ request('expiring_days') == '30' ? 'selected' : '' }}>Kedaluwarsa 30 hari</option>
                                    <option value="60" {{ request('expiring_days') == '60' ? 'selected' : '' }}>Kedaluwarsa 60 hari</option>
                                    <option value="90" {{ request('expiring_days') == '90' ? 'selected' : '' }}>Kedaluwarsa 90 hari</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="expired" value="1" id="expiredCheck"
                                           {{ request('expired') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="expiredCheck">
                                        Sudah Kedaluwarsa
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Active Filter Chips --}}
    @if(request()->hasAny(['search', 'type_id', 'status', 'unit_id', 'category_id', 'date_from', 'date_to', 'expiring_days', 'expired']))
    <div class="mb-4">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="text-muted small me-2">Filter aktif:</span>
            
            @if(request('search'))
                <a href="{{ request()->fullUrlWithoutQuery('search') }}" class="badge bg-primary-soft text-primary d-inline-flex align-items-center gap-1 text-decoration-none">
                    Pencarian: "{{ Str::limit(request('search'), 20) }}"
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
            
            @if(request('type_id'))
                <a href="{{ request()->fullUrlWithoutQuery('type_id') }}" class="badge bg-info-soft text-info d-inline-flex align-items-center gap-1 text-decoration-none">
                    Jenis: {{ $types->firstWhere('id', request('type_id'))?->name ?? 'N/A' }}
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
            
            @if(request('status'))
                <a href="{{ request()->fullUrlWithoutQuery('status') }}" class="badge bg-warning-soft text-warning d-inline-flex align-items-center gap-1 text-decoration-none">
                    Status: {{ $statuses[request('status')] ?? request('status') }}
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
            
            @if(request('unit_id'))
                <a href="{{ request()->fullUrlWithoutQuery('unit_id') }}" class="badge bg-secondary-soft text-secondary d-inline-flex align-items-center gap-1 text-decoration-none">
                    Unit: {{ $units->firstWhere('id', request('unit_id'))?->name ?? 'N/A' }}
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
            
            @if(request('category_id'))
                <a href="{{ request()->fullUrlWithoutQuery('category_id') }}" class="badge bg-success-soft text-success d-inline-flex align-items-center gap-1 text-decoration-none">
                    Kategori: {{ $categories->firstWhere('id', request('category_id'))?->name ?? 'N/A' }}
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
            
            @if(request('date_from'))
                <a href="{{ request()->fullUrlWithoutQuery('date_from') }}" class="badge bg-secondary-soft text-secondary d-inline-flex align-items-center gap-1 text-decoration-none">
                    Dari: {{ \Carbon\Carbon::parse(request('date_from'))->format('d M Y') }}
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
            
            @if(request('date_to'))
                <a href="{{ request()->fullUrlWithoutQuery('date_to') }}" class="badge bg-secondary-soft text-secondary d-inline-flex align-items-center gap-1 text-decoration-none">
                    Sampai: {{ \Carbon\Carbon::parse(request('date_to'))->format('d M Y') }}
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
            
            @if(request('expiring_days'))
                <a href="{{ request()->fullUrlWithoutQuery('expiring_days') }}" class="badge bg-warning-soft text-warning d-inline-flex align-items-center gap-1 text-decoration-none">
                    Kedaluwarsa: {{ request('expiring_days') }} hari
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
            
            @if(request('expired'))
                <a href="{{ request()->fullUrlWithoutQuery('expired') }}" class="badge bg-danger-soft text-danger d-inline-flex align-items-center gap-1 text-decoration-none">
                    Sudah Kedaluwarsa
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
            
            <a href="{{ route('documents.index') }}" class="badge bg-light text-dark d-inline-flex align-items-center gap-1 text-decoration-none border">
                <i class="bi bi-x-circle me-1"></i>Hapus Semua Filter
            </a>
        </div>
    </div>
    @endif

    {{-- Status Legend --}}
    <div class="mb-4" x-data="{ showLegend: false }">
        <button type="button" 
                class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2"
                @click="showLegend = !showLegend">
            <i class="bi bi-question-circle"></i>
            <span>Keterangan Warna Status</span>
            <i class="bi bi-chevron-down transition-transform" :class="{ 'rotate-180': showLegend }"></i>
        </button>
        
        <div x-show="showLegend" x-collapse x-cloak class="mt-3">
            <div class="glass-card">
                <div class="card-body py-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <h6 class="text-muted mb-3 small text-uppercase">Status Dokumen</h6>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary">Draft</span>
                                <small class="text-muted">Dokumen masih dalam tahap penyusunan</small>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-info">Menunggu Review</span>
                                <small class="text-muted">Dokumen sedang direview</small>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-warning text-dark">Menunggu Approval</span>
                                <small class="text-muted">Menunggu persetujuan</small>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success">Dipublikasikan</span>
                                <small class="text-muted">Dokumen aktif dan berlaku</small>
                            </div>
                        </div>
                        
                        <div class="col-12 mt-3">
                            <h6 class="text-muted mb-3 small text-uppercase">Status Masa Berlaku</h6>
                        </div>
                        <div class="col-md-6 col-lg-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success-soft text-success">Aktif</span>
                                <small class="text-muted">> 6 bulan</small>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary-soft text-primary">Perhatian</span>
                                <small class="text-muted">≤ 6 bulan</small>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-warning-soft text-warning">Peringatan</span>
                                <small class="text-muted">≤ 3 bulan</small>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-danger-soft text-danger">Kritis</span>
                                <small class="text-muted">≤ 1 bulan</small>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-danger">Kedaluwarsa</span>
                                <small class="text-muted">Sudah lewat</small>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-purple-soft text-purple">Permanen</span>
                                <small class="text-muted">Tanpa batas</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Documents Table View --}}
    <div class="glass-card" x-show="viewMode === 'table'" x-cloak>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" class="form-check-input" 
                                       @change="toggleSelectAll($event.target.checked)"
                                       :checked="isAllSelected"
                                       :indeterminate.prop="isIndeterminate">
                            </th>
                            <x-table-header column="document_number" :sortable="true" :currentSort="request('sort')" :currentDir="request('dir')">
                                Nomor
                            </x-table-header>
                            <x-table-header column="title" :sortable="true" :currentSort="request('sort')" :currentDir="request('dir')">
                                Judul
                            </x-table-header>
                            <th>Jenis</th>
                            <th>Unit</th>
                            <x-table-header column="document_date" :sortable="true" :currentSort="request('sort')" :currentDir="request('dir')">
                                Tanggal
                            </x-table-header>
                            <th>Status</th>
                            <x-table-header column="updated_at" :sortable="true" :currentSort="request('sort')" :currentDir="request('dir')">
                                Terakhir Update
                            </x-table-header>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $document)
                        <tr :class="{ 'table-primary': selectedIds.includes({{ $document->id }}) }">
                            <td>
                                <input type="checkbox" class="form-check-input"
                                       value="{{ $document->id }}"
                                       @change="toggleSelect({{ $document->id }})"
                                       :checked="selectedIds.includes({{ $document->id }})">
                            </td>
                            <td>
                                <span class="font-monospace">
                                    @if(request('search'))
                                        {!! preg_replace('/(' . preg_quote(request('search'), '/') . ')/i', '<mark class="bg-warning-subtle px-1 rounded">$1</mark>', e($document->document_number)) !!}
                                    @else
                                        {{ $document->document_number }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <div>
                                    <a href="{{ route('documents.show', $document) }}" class="text-decoration-none fw-medium">
                                        @if(request('search'))
                                            {!! preg_replace('/(' . preg_quote(request('search'), '/') . ')/i', '<mark class="bg-warning-subtle px-1 rounded">$1</mark>', e(Str::limit($document->title, 50))) !!}
                                        @else
                                            {{ Str::limit($document->title, 50) }}
                                        @endif
                                    </a>
                                    @if($document->isExpired())
                                        <span class="badge bg-danger ms-1">Kedaluwarsa</span>
                                    @elseif($document->expiry_date && $document->expiry_date->diffInDays(now()) <= 30)
                                        <span class="badge bg-warning text-dark ms-1">Segera Kedaluwarsa</span>
                                    @endif
                                </div>
                                @if($document->category)
                                    <small class="text-muted">{{ $document->category->name }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $document->type->name ?? '-' }}</span>
                            </td>
                            <td>{{ $document->unit->name ?? '-' }}</td>
                            <td>{{ $document->document_date?->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'pending_review' => 'info',
                                        'pending_approval' => 'warning',
                                        'approved' => 'primary',
                                        'published' => 'success',
                                        'expired' => 'danger',
                                        'archived' => 'dark',
                                        'rejected' => 'danger',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$document->status] ?? 'secondary' }}">
                                    {{ $statuses[$document->status] ?? $document->status }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $document->updated_at->diffForHumans() }}</small>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('documents.show', $document) }}">
                                                <i class="bi bi-eye me-2"></i>Lihat
                                            </a>
                                        </li>
                                        @if($document->currentVersion)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('documents.download', $document) }}">
                                                <i class="bi bi-download me-2"></i>Download
                                            </a>
                                        </li>
                                        @endif
                                        @can('documents.edit')
                                        @if($document->isEditable())
                                        <li>
                                            <a class="dropdown-item" href="{{ route('documents.edit', $document) }}">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a>
                                        </li>
                                        @endif
                                        @endcan
                                        @can('documents.delete')
                                        @if($document->status === 'draft')
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('documents.destroy', $document) }}" method="POST" 
                                                  onsubmit="return confirm('Yakin ingin menghapus dokumen ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>Hapus
                                                </button>
                                            </form>
                                        </li>
                                        @endif
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-file-earmark-text fs-1 d-block mb-3 opacity-25"></i>
                                    <p class="mb-2">Tidak ada dokumen ditemukan.</p>
                                    @can('documents.create')
                                    <a href="{{ route('documents.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i>Tambah Dokumen Pertama
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($documents->hasPages())
        <div class="card-footer bg-transparent">
            {{ $documents->withQueryString()->links() }}
        </div>
        @endif
    </div>

    {{-- Documents Card View --}}
    <div x-show="viewMode === 'card'" x-cloak>
        @if($documents->count() > 0)
        <div class="row g-4">
            @foreach($documents as $document)
            @php
                $statusColors = [
                    'draft' => 'secondary',
                    'pending_review' => 'info',
                    'pending_approval' => 'warning',
                    'approved' => 'primary',
                    'published' => 'success',
                    'expired' => 'danger',
                    'archived' => 'dark',
                    'rejected' => 'danger',
                ];
                $isExpired = $document->isExpired();
                $isExpiringSoon = !$isExpired && $document->expiry_date && $document->expiry_date->diffInDays(now()) <= 30;
            @endphp
            <div class="col-md-6 col-lg-4 col-xl-3">
                {{-- Swipeable Card Container for Mobile --}}
                <div class="swipe-container" 
                     x-data="swipeableCard({ documentId: {{ $document->id }}, hasDownload: {{ $document->currentVersion ? 'true' : 'false' }}, canEdit: {{ $document->isEditable() ? 'true' : 'false' }} })"
                     @touchstart="handleTouchStart"
                     @touchmove="handleTouchMove"
                     @touchend="handleTouchEnd"
                     :class="{ 'swiping-left': swipeDirection === 'left', 'swiping-right': swipeDirection === 'right' }">
                    
                    {{-- Left Actions (View, Download) - Revealed on swipe right --}}
                    <div class="swipe-actions-left" :class="{ 'visible': swipeOffset > 20 }">
                        <a href="{{ route('documents.show', $document) }}" class="swipe-action swipe-action-view">
                            <i class="bi bi-eye"></i>
                            <span>Lihat</span>
                        </a>
                        @if($document->currentVersion)
                        <a href="{{ route('documents.download', $document) }}" class="swipe-action swipe-action-download">
                            <i class="bi bi-download"></i>
                            <span>Unduh</span>
                        </a>
                        @endif
                    </div>
                    
                    {{-- Right Actions (Edit, More) - Revealed on swipe left --}}
                    <div class="swipe-actions-right" :class="{ 'visible': swipeOffset < -20 }">
                        @can('documents.edit')
                        @if($document->isEditable())
                        <a href="{{ route('documents.edit', $document) }}" class="swipe-action swipe-action-edit">
                            <i class="bi bi-pencil"></i>
                            <span>Edit</span>
                        </a>
                        @endif
                        @endcan
                        <button type="button" class="swipe-action swipe-action-share" @click="shareDocument()">
                            <i class="bi bi-share"></i>
                            <span>Bagikan</span>
                        </button>
                    </div>
                    
                    {{-- Swipe Feedback Overlays --}}
                    <div class="swipe-feedback swipe-feedback-left"></div>
                    <div class="swipe-feedback swipe-feedback-right"></div>
                    
                    {{-- Card Content --}}
                    <div class="swipe-content glass-card h-100 transition-all hover:shadow-lg {{ $isExpired ? 'border-danger' : ($isExpiringSoon ? 'border-warning' : '') }}"
                         :class="{ 'swiping': isSwiping }"
                         :style="{ transform: `translateX(${swipeOffset}px)` }">
                    <div class="card-body">
                        {{-- Status & Type Badges --}}
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-{{ $statusColors[$document->status] ?? 'secondary' }}">
                                {{ $statuses[$document->status] ?? $document->status }}
                            </span>
                            @if($isExpired)
                                <span class="badge bg-danger">Kedaluwarsa</span>
                            @elseif($isExpiringSoon)
                                <span class="badge bg-warning text-dark">Segera</span>
                            @endif
                        </div>
                        
                        {{-- Document Number --}}
                        <p class="font-monospace text-muted small mb-2">
                            @if(request('search'))
                                {!! preg_replace('/(' . preg_quote(request('search'), '/') . ')/i', '<mark class="bg-warning-subtle px-1 rounded">$1</mark>', e($document->document_number)) !!}
                            @else
                                {{ $document->document_number }}
                            @endif
                        </p>
                        
                        {{-- Title --}}
                        <h6 class="card-title mb-3">
                            <a href="{{ route('documents.show', $document) }}" class="text-decoration-none stretched-link">
                                @if(request('search'))
                                    {!! preg_replace('/(' . preg_quote(request('search'), '/') . ')/i', '<mark class="bg-warning-subtle px-1 rounded">$1</mark>', e(Str::limit($document->title, 60))) !!}
                                @else
                                    {{ Str::limit($document->title, 60) }}
                                @endif
                            </a>
                        </h6>
                        
                        {{-- Metadata --}}
                        <div class="small text-muted">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-folder2 text-primary"></i>
                                {{ $document->type->name ?? '-' }}
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-building text-primary"></i>
                                {{ Str::limit($document->unit->name ?? '-', 25) }}
                            </div>
                            @if($document->expiry_date)
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-calendar-event {{ $isExpired ? 'text-danger' : ($isExpiringSoon ? 'text-warning' : 'text-primary') }}"></i>
                                <span class="{{ $isExpired ? 'text-danger' : ($isExpiringSoon ? 'text-warning' : '') }}">
                                    {{ $document->expiry_date->format('d M Y') }}
                                    @if($isExpired)
                                        ({{ abs($document->expiry_date->diffInDays(now())) }} hari lalu)
                                    @else
                                        ({{ $document->expiry_date->diffInDays(now()) }} hari lagi)
                                    @endif
                                </span>
                            </div>
                            @else
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-infinity text-success"></i>
                                <span class="text-success">Permanen</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Card Footer --}}
                    <div class="card-footer bg-transparent border-top-0 pt-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>{{ $document->updated_at->diffForHumans() }}
                            </small>
                            <div class="dropdown" style="position: relative; z-index: 10;">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" @click.stop>
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('documents.show', $document) }}" @click.stop>
                                            <i class="bi bi-eye me-2"></i>Lihat
                                        </a>
                                    </li>
                                    @if($document->currentVersion)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('documents.download', $document) }}" @click.stop>
                                            <i class="bi bi-download me-2"></i>Download
                                        </a>
                                    </li>
                                    @endif
                                    @can('documents.edit')
                                    @if($document->isEditable())
                                    <li>
                                        <a class="dropdown-item" href="{{ route('documents.edit', $document) }}" @click.stop>
                                            <i class="bi bi-pencil me-2"></i>Edit
                                        </a>
                                    </li>
                                    @endif
                                    @endcan
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>{{-- End swipe-content --}}
                </div>{{-- End swipe-container --}}
            </div>
            @endforeach
        </div>
        
        @if($documents->hasPages())
        <div class="mt-4">
            {{ $documents->withQueryString()->links() }}
        </div>
        @endif
        @else
        <div class="glass-card text-center py-5">
            <div class="text-muted">
                <i class="bi bi-file-earmark-text fs-1 d-block mb-3 opacity-25"></i>
                <p class="mb-2">Tidak ada dokumen ditemukan.</p>
                @can('documents.create')
                <a href="{{ route('documents.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Dokumen Pertama
                </a>
                @endcan
            </div>
        </div>
        @endif
    </div>

    {{-- Bulk Delete Confirmation Modal --}}
    <div x-show="showDeleteModal" x-cloak
         class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>Konfirmasi Hapus
                    </h5>
                    <button type="button" class="btn-close" @click="showDeleteModal = false"></button>
                </div>
                <div class="modal-body">
                    <p>Anda akan menghapus <strong x-text="selectedIds.length"></strong> dokumen.</p>
                    <p class="text-muted small mb-0">
                        Dokumen yang dihapus dapat dipulihkan dari arsip dalam 30 hari.
                    </p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" @click="showDeleteModal = false">Batal</button>
                    <button type="button" class="btn btn-danger" @click="executeBulkDelete()" :disabled="isDeleting">
                        <span x-show="!isDeleting">
                            <i class="bi bi-trash me-1"></i>Hapus <span x-text="selectedIds.length"></span> Dokumen
                        </span>
                        <span x-show="isDeleting">
                            <span class="spinner-border spinner-border-sm me-1"></span>Menghapus...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Swipeable Card Component for Mobile
function swipeableCard(config) {
    return {
        documentId: config.documentId,
        hasDownload: config.hasDownload,
        canEdit: config.canEdit,
        
        // Swipe state
        isSwiping: false,
        swipeOffset: 0,
        swipeDirection: null,
        startX: 0,
        startY: 0,
        currentX: 0,
        isHorizontalSwipe: null,
        
        // Touch thresholds
        minSwipeDistance: 10,
        maxSwipeDistance: 160,
        actionThreshold: 80,
        
        handleTouchStart(e) {
            // Only handle single touch
            if (e.touches.length !== 1) return;
            
            // Check if we're on mobile
            if (window.innerWidth >= 1024) return;
            
            const touch = e.touches[0];
            this.startX = touch.clientX;
            this.startY = touch.clientY;
            this.isHorizontalSwipe = null;
            this.isSwiping = false;
        },
        
        handleTouchMove(e) {
            if (!this.startX || window.innerWidth >= 1024) return;
            
            const touch = e.touches[0];
            const deltaX = touch.clientX - this.startX;
            const deltaY = touch.clientY - this.startY;
            
            // Determine swipe direction on first significant move
            if (this.isHorizontalSwipe === null) {
                if (Math.abs(deltaX) > 5 || Math.abs(deltaY) > 5) {
                    this.isHorizontalSwipe = Math.abs(deltaX) > Math.abs(deltaY);
                }
            }
            
            // Only handle horizontal swipes
            if (!this.isHorizontalSwipe) return;
            
            // Prevent vertical scroll during horizontal swipe
            e.preventDefault();
            
            this.isSwiping = true;
            this.currentX = touch.clientX;
            
            // Calculate offset with resistance at edges
            let offset = deltaX;
            
            // Apply resistance at max distance
            if (Math.abs(offset) > this.maxSwipeDistance) {
                const overflow = Math.abs(offset) - this.maxSwipeDistance;
                const dampedOverflow = Math.sqrt(overflow) * 3;
                offset = (offset > 0 ? 1 : -1) * (this.maxSwipeDistance + dampedOverflow);
            }
            
            this.swipeOffset = offset;
            this.swipeDirection = offset > 0 ? 'right' : 'left';
        },
        
        handleTouchEnd(e) {
            if (!this.isSwiping || window.innerWidth >= 1024) {
                this.resetSwipe();
                return;
            }
            
            const deltaX = this.currentX - this.startX;
            
            // Check if swipe exceeds action threshold
            if (Math.abs(deltaX) >= this.actionThreshold) {
                // Trigger action based on direction
                if (deltaX > 0) {
                    // Swiped right - View action (primary)
                    this.triggerAction('view');
                } else {
                    // Swiped left - Edit or Share action
                    this.triggerAction('secondary');
                }
            }
            
            // Animate back to center
            this.resetSwipe();
        },
        
        triggerAction(actionType) {
            if (actionType === 'view') {
                // Navigate to document view
                window.location.href = `/documents/${this.documentId}`;
            } else if (actionType === 'secondary') {
                // Show share dialog or trigger edit
                if (this.canEdit) {
                    window.location.href = `/documents/${this.documentId}/edit`;
                } else {
                    this.shareDocument();
                }
            }
        },
        
        shareDocument() {
            const shareUrl = `${window.location.origin}/documents/${this.documentId}`;
            const shareTitle = 'Dokumen Hukum';
            
            if (navigator.share) {
                navigator.share({
                    title: shareTitle,
                    url: shareUrl
                }).catch(err => console.log('Share cancelled:', err));
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(shareUrl).then(() => {
                    this.showToast('Link disalin ke clipboard');
                }).catch(() => {
                    // Final fallback
                    prompt('Salin link berikut:', shareUrl);
                });
            }
        },
        
        showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'position-fixed bg-dark text-white text-sm px-4 py-2 rounded-lg shadow-lg';
            toast.style.cssText = 'bottom: 100px; left: 50%; transform: translateX(-50%); z-index: 9999;';
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.transition = 'opacity 0.3s';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 2000);
        },
        
        resetSwipe() {
            this.isSwiping = false;
            this.swipeOffset = 0;
            this.swipeDirection = null;
            this.startX = 0;
            this.startY = 0;
            this.currentX = 0;
            this.isHorizontalSwipe = null;
        }
    };
}

function documentsIndex() {
    return {
        viewMode: localStorage.getItem('documentsViewMode') || 'table',
        selectedIds: [],
        showDeleteModal: false,
        isDeleting: false,
        documentIds: @json($documents->pluck('id')->toArray()),
        
        get isAllSelected() {
            return this.documentIds.length > 0 && this.selectedIds.length === this.documentIds.length;
        },
        
        get isIndeterminate() {
            return this.selectedIds.length > 0 && this.selectedIds.length < this.documentIds.length;
        },
        
        get canArchive() {
            return this.selectedIds.length > 0;
        },
        
        toggleSelect(id) {
            const index = this.selectedIds.indexOf(id);
            if (index > -1) {
                this.selectedIds.splice(index, 1);
            } else {
                this.selectedIds.push(id);
            }
        },
        
        toggleSelectAll(checked) {
            if (checked) {
                this.selectedIds = [...this.documentIds];
            } else {
                this.selectedIds = [];
            }
        },
        
        clearSelection() {
            this.selectedIds = [];
        },
        
        bulkExport(format) {
            const ids = this.selectedIds.join(',');
            const url = format === 'excel' 
                ? `{{ route('documents.export') }}?ids=${ids}`
                : `{{ route('documents.export-pdf') }}?ids=${ids}`;
            window.location.href = url;
        },
        
        bulkArchive() {
            if (!confirm(`Arsipkan ${this.selectedIds.length} dokumen?`)) return;
            
            fetch('{{ route('documents.bulk-archive') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids: this.selectedIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal mengarsipkan dokumen');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            });
        },
        
        confirmBulkDelete() {
            this.showDeleteModal = true;
        },
        
        executeBulkDelete() {
            this.isDeleting = true;
            
            fetch('{{ route('documents.bulk-delete') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids: this.selectedIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal menghapus dokumen');
                    this.isDeleting = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
                this.isDeleting = false;
            });
        }
    }
}

// Filter Presets Alpine Component
function filterPresets() {
    return {
        presets: JSON.parse(localStorage.getItem('documentFilterPresets') || '[]'),
        newPresetName: '',
        
        savePreset() {
            const name = this.newPresetName.trim();
            if (!name) return;
            
            // Get current filter values from form
            const form = document.getElementById('filterForm');
            const formData = new FormData(form);
            const filters = {};
            
            for (const [key, value] of formData.entries()) {
                if (value) {
                    filters[key] = value;
                }
            }
            
            // Check if no filters
            if (Object.keys(filters).length === 0) {
                alert('Tidak ada filter untuk disimpan');
                return;
            }
            
            // Check for duplicate name
            const existingIndex = this.presets.findIndex(p => p.name.toLowerCase() === name.toLowerCase());
            if (existingIndex >= 0) {
                if (confirm(`Preset "${name}" sudah ada. Ganti dengan filter baru?`)) {
                    this.presets[existingIndex] = { name, filters };
                } else {
                    return;
                }
            } else {
                this.presets.push({ name, filters });
            }
            
            // Save to localStorage
            localStorage.setItem('documentFilterPresets', JSON.stringify(this.presets));
            this.newPresetName = '';
            
            // Show success message
            this.showToast(`Preset "${name}" berhasil disimpan`);
        },
        
        applyPreset(preset) {
            const params = new URLSearchParams(preset.filters);
            window.location.href = `{{ route('documents.index') }}?${params.toString()}`;
        },
        
        deletePreset(index) {
            const preset = this.presets[index];
            if (confirm(`Hapus preset "${preset.name}"?`)) {
                this.presets.splice(index, 1);
                localStorage.setItem('documentFilterPresets', JSON.stringify(this.presets));
            }
        },
        
        showToast(message) {
            // Simple toast notification
            const toast = document.createElement('div');
            toast.className = 'position-fixed bottom-0 end-0 p-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <div class="toast show align-items-center text-bg-success border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-check-circle me-2"></i>${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
    }
}

// Search Autocomplete Alpine Component
function searchAutocomplete() {
    return {
        query: '{{ request('search') }}',
        suggestions: [],
        showDropdown: false,
        isLoading: false,
        selectedIndex: -1,
        
        async fetchSuggestions() {
            if (this.query.length < 2) {
                this.suggestions = [];
                this.showDropdown = false;
                return;
            }
            
            this.isLoading = true;
            
            try {
                const response = await fetch(`{{ route('documents.search-suggestions') }}?q=${encodeURIComponent(this.query)}`);
                this.suggestions = await response.json();
                this.showDropdown = this.suggestions.length > 0;
                this.selectedIndex = -1;
                
                // Save to recent searches
                this.saveRecentSearch(this.query);
            } catch (error) {
                console.error('Search error:', error);
                this.suggestions = [];
            } finally {
                this.isLoading = false;
            }
        },
        
        navigateSuggestion(direction) {
            if (!this.showDropdown || this.suggestions.length === 0) return;
            
            this.selectedIndex += direction;
            
            if (this.selectedIndex < 0) {
                this.selectedIndex = this.suggestions.length - 1;
            } else if (this.selectedIndex >= this.suggestions.length) {
                this.selectedIndex = 0;
            }
        },
        
        selectSuggestion() {
            if (this.selectedIndex >= 0 && this.suggestions[this.selectedIndex]) {
                window.location.href = this.suggestions[this.selectedIndex].url;
            }
        },
        
        saveRecentSearch(query) {
            if (!query || query.length < 2) return;
            
            let recentSearches = JSON.parse(localStorage.getItem('documentRecentSearches') || '[]');
            
            // Remove duplicates and add to front
            recentSearches = recentSearches.filter(s => s.toLowerCase() !== query.toLowerCase());
            recentSearches.unshift(query);
            
            // Keep only last 10 searches
            recentSearches = recentSearches.slice(0, 10);
            
            localStorage.setItem('documentRecentSearches', JSON.stringify(recentSearches));
        }
    }
}

// Multi-column Sort Handler
function handleMultiSort(event, sortKey, currentDir) {
    // If Shift key is NOT pressed, allow normal link behavior (single sort)
    if (!event.shiftKey) {
        return true;
    }
    
    // Shift+click: multi-column sort
    event.preventDefault();
    
    const url = new URL(window.location.href);
    let sorts = url.searchParams.get('sort') ? url.searchParams.get('sort').split(',') : [];
    let dirs = url.searchParams.get('dir') ? url.searchParams.get('dir').split(',') : [];
    
    const existingIndex = sorts.indexOf(sortKey);
    
    if (existingIndex !== -1) {
        // Column already in sort - toggle direction
        dirs[existingIndex] = dirs[existingIndex] === 'asc' ? 'desc' : 'asc';
    } else {
        // Add new column to sort
        sorts.push(sortKey);
        dirs.push('asc');
    }
    
    // Limit to max 3 sort columns for performance
    if (sorts.length > 3) {
        sorts = sorts.slice(-3);
        dirs = dirs.slice(-3);
    }
    
    url.searchParams.set('sort', sorts.join(','));
    url.searchParams.set('dir', dirs.join(','));
    
    window.location.href = url.toString();
    return false;
}

// Clear multi-sort (double-click on any sorted header)
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.multi-sort-header').forEach(header => {
        header.addEventListener('dblclick', function(e) {
            e.preventDefault();
            const url = new URL(window.location.href);
            url.searchParams.delete('sort');
            url.searchParams.delete('dir');
            window.location.href = url.toString();
        });
    });
    
    // Show multi-sort hint tooltip
    const sortHeaders = document.querySelectorAll('.multi-sort-header');
    if (sortHeaders.length > 0) {
        const urlParams = new URLSearchParams(window.location.search);
        const currentSorts = urlParams.get('sort') ? urlParams.get('sort').split(',') : [];
        
        if (currentSorts.length > 1) {
            // Show a small info badge when multi-sort is active
            const badge = document.createElement('div');
            badge.className = 'fixed bottom-4 left-4 bg-primary-500/90 text-white text-xs px-3 py-2 rounded-lg shadow-lg z-50 flex items-center gap-2';
            badge.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                </svg>
                <span>Multi-sort aktif (${currentSorts.length} kolom)</span>
                <button onclick="clearMultiSort()" class="ml-2 hover:text-primary-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;
            document.body.appendChild(badge);
        }
    }
});

function clearMultiSort() {
    const url = new URL(window.location.href);
    url.searchParams.delete('sort');
    url.searchParams.delete('dir');
    window.location.href = url.toString();
}

// Sort Preference Management
(function() {
    const SORT_STORAGE_KEY = 'documentSortPreference';
    
    // Save sort preference when clicking on sortable headers
    document.querySelectorAll('th a[href*="sort="]').forEach(link => {
        link.addEventListener('click', function(e) {
            const url = new URL(this.href);
            const sort = url.searchParams.get('sort');
            const direction = url.searchParams.get('direction') || url.searchParams.get('dir') || 'asc';
            
            if (sort) {
                localStorage.setItem(SORT_STORAGE_KEY, JSON.stringify({ sort, direction }));
            }
        });
    });
    
    // Apply saved sort preference on page load (only if no sort in URL)
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.has('sort') && !urlParams.has('dir')) {
        const savedSort = localStorage.getItem(SORT_STORAGE_KEY);
        if (savedSort) {
            try {
                const { sort, direction } = JSON.parse(savedSort);
                if (sort) {
                    // Only redirect if we have filters or it's the first visit
                    const hasFilters = urlParams.toString().length > 0;
                    if (!hasFilters && window.location.pathname.includes('/documents')) {
                        urlParams.set('sort', sort);
                        urlParams.set('dir', direction);
                        // Don't auto-redirect on first visit to avoid infinite loops
                        // Just update the URL if user navigates
                    }
                }
            } catch (e) {
                console.error('Error parsing saved sort preference:', e);
            }
        }
    } else {
        // Save current sort to localStorage
        const sort = urlParams.get('sort');
        const direction = urlParams.get('dir') || 'asc';
        if (sort) {
            localStorage.setItem(SORT_STORAGE_KEY, JSON.stringify({ sort, direction }));
        }
    }
})();
</script>
@endpush
@endsection
