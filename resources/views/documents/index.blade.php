@extends('layouts.app')

@section('title', 'Daftar Dokumen')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Dokumen']
    ]" />
@endsection

@section('content')
<div class="space-y-6" x-data="documentsIndex()" x-init="$watch('viewMode', value => localStorage.setItem('documentsViewMode', value))">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Dokumen</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">Kelola dokumen hukum organisasi</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            {{-- View Toggle --}}
            <div class="inline-flex overflow-hidden rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)]">
                <button type="button"
                        class="px-3 py-2 text-sm text-[var(--text-secondary)] dark:text-[var(--text-tertiary)] transition-colors hover:text-[var(--text-primary)]"
                        :class="{ 'bg-[var(--surface-glass-elevated)] text-[var(--text-primary)]': viewMode === 'table' }"
                        @click="viewMode = 'table'"
                        title="Tampilan Tabel">
                    <i class="bi bi-list-ul"></i>
                </button>
                <button type="button"
                        class="px-3 py-2 text-sm text-[var(--text-secondary)] dark:text-[var(--text-tertiary)] transition-colors hover:text-[var(--text-primary)]"
                        :class="{ 'bg-[var(--surface-glass-elevated)] text-[var(--text-primary)]': viewMode === 'card' }"
                        @click="viewMode = 'card'"
                        title="Tampilan Kartu">
                    <i class="bi bi-grid-3x3-gap"></i>
                </button>
            </div>
            @can('documents.create')
                <x-button href="{{ route('documents.create') }}">
                    Tambah Dokumen
                </x-button>
            @endcan
        </div>
    </div>

    {{-- Bulk Actions Toolbar --}}
    <x-glass-card :hover="false" x-show="selectedIds.length > 0" x-cloak x-transition class="border border-[var(--surface-glass-border-hover)] p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2 text-sm">
                <x-badge type="info" size="sm" x-text="selectedIds.length"></x-badge>
                <span class="text-[var(--text-secondary)]">dokumen dipilih</span>
                <button type="button" class="text-sm text-[var(--text-tertiary)] hover:text-[var(--text-primary)]" @click="clearSelection()">
                    Batalkan pilihan
                </button>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('documents.view')
                    <x-button type="button" size="sm" variant="secondary" @click="bulkExport('excel')">
                        Export Excel
                    </x-button>
                    <x-button type="button" size="sm" variant="secondary" @click="bulkExport('pdf')">
                        Export PDF
                    </x-button>
                @endcan
                @can('documents.edit')
                    <x-button type="button" size="sm" variant="secondary" @click="bulkArchive()" x-bind:disabled="!canArchive">
                        Arsipkan
                    </x-button>
                @endcan
                @can('documents.delete')
                    <x-button type="button" size="sm" variant="danger" @click="confirmBulkDelete()">
                        Hapus
                    </x-button>
                @endcan
            </div>
        </div>
    </x-glass-card>

    {{-- Filters --}}
    <x-glass-card :hover="false" class="p-6" x-data="{ advancedOpen: {{ request()->hasAny(['date_from', 'date_to', 'category_id', 'expired', 'expiring_days']) ? 'true' : 'false' }} }">
        <form action="{{ route('documents.index') }}" method="GET" id="filterForm" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <label class="text-sm font-medium text-[var(--text-primary)]">Pencarian</label>
                    <div class="relative mt-2" x-data="searchAutocomplete()">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]"></i>
                        <input type="text" class="glass-input pl-10" name="search"
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

                        {{-- Autocomplete Dropdown --}}
                        <div x-show="showDropdown && suggestions.length > 0"
                             x-cloak
                             @click.outside="showDropdown = false"
                             class="glass-dropdown w-full max-h-[350px] overflow-y-auto">
                            <template x-for="(suggestion, index) in suggestions" :key="suggestion.id">
                                <a :href="suggestion.url"
                                   class="flex items-start gap-3 px-4 py-2 transition-colors"
                                   :class="{ 'bg-[var(--surface-glass)]': selectedIndex === index }"
                                   @mouseenter="selectedIndex = index">
                                    <i class="bi bi-file-earmark-text text-primary-500 mt-1"></i>
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-[var(--text-primary)] truncate" x-text="suggestion.title"></div>
                                        <div class="mt-1 flex items-center gap-2 text-xs text-[var(--text-tertiary)]">
                                            <span x-text="suggestion.document_number"></span>
                                            <span>•</span>
                                            <span x-text="suggestion.type"></span>
                                        </div>
                                    </div>
                                </a>
                            </template>
                            <div class="border-t border-[var(--surface-glass-border)] px-4 py-2 text-center text-xs text-[var(--text-tertiary)]">
                                Gunakan ↑↓ untuk navigasi, Enter untuk memilih
                            </div>
                        </div>

                        {{-- Loading indicator --}}
                        <div x-show="isLoading" class="absolute right-3 top-1/2 -translate-y-1/2">
                            <div class="h-4 w-4 animate-spin rounded-full border-2 border-[var(--surface-glass-border)] border-t-primary-500"></div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <label class="text-sm font-medium text-[var(--text-primary)]">Jenis Dokumen</label>
                    <select name="type_id" class="glass-input mt-2">
                        <option value="">Semua Jenis</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" {{ request('type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label class="text-sm font-medium text-[var(--text-primary)]">Status</label>
                    <select name="status" class="glass-input mt-2">
                        <option value="">Semua Status</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label class="text-sm font-medium text-[var(--text-primary)]">Unit</label>
                    <select name="unit_id" class="glass-input mt-2">
                        <option value="">Semua Unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2 flex flex-wrap items-end gap-2">
                    <x-button type="submit" size="sm">Filter</x-button>
                    <x-button href="{{ route('documents.index') }}" size="sm" variant="secondary">Reset</x-button>

                    {{-- Filter Presets Dropdown --}}
                    <div x-data="filterPresets()">
                        <x-dropdown align="right" width="64">
                            <x-slot name="trigger">
                                <button class="btn-secondary px-3 py-2 rounded-lg" type="button" aria-label="Filter presets">
                                    <i class="bi bi-bookmark"></i>
                                </button>
                            </x-slot>

                            <div class="px-4 py-2 border-b border-[var(--surface-glass-border)]">
                                <p class="text-xs font-semibold text-[var(--text-tertiary)] uppercase">Preset Filter</p>
                            </div>
                            <div class="px-4 py-3 space-y-2">
                                <div class="flex items-center gap-2">
                                    <input type="text" class="glass-input h-9" placeholder="Nama preset..." x-model="newPresetName" @keydown.enter.prevent="savePreset()">
                                    <button class="btn-primary px-3 py-2 rounded-lg" type="button" @click="savePreset()" :disabled="!newPresetName.trim()">
                                        <i class="bi bi-save"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="border-t border-[var(--surface-glass-border)]"></div>
                            <div class="py-2">
                                <template x-if="presets.length === 0">
                                    <div class="px-4 py-3 text-center text-xs text-[var(--text-tertiary)]">
                                        Belum ada preset tersimpan
                                    </div>
                                </template>
                                <template x-for="(preset, index) in presets" :key="index">
                                    <div class="px-4 py-2 flex items-center justify-between">
                                        <button type="button" class="text-sm text-[var(--text-primary)] hover:text-primary-500" @click="applyPreset(preset)">
                                            <i class="bi bi-bookmark-fill mr-2 text-primary-500"></i>
                                            <span x-text="preset.name"></span>
                                        </button>
                                        <button type="button" class="text-sm text-red-500 hover:text-red-600" @click.stop="deletePreset(index)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </x-dropdown>
                    </div>

                    {{-- Export Dropdown --}}
                    <x-dropdown align="right" width="56">
                        <x-slot name="trigger">
                            <button class="btn-secondary px-3 py-2 rounded-lg" type="button" aria-label="Export">
                                <i class="bi bi-download"></i>
                            </button>
                        </x-slot>
                        <x-dropdown-item href="{{ route('documents.export', request()->query()) }}">
                            Export ke Excel
                        </x-dropdown-item>
                        <x-dropdown-item href="{{ route('documents.export-pdf', request()->query()) }}">
                            Export ke PDF
                        </x-dropdown-item>
                    </x-dropdown>
                </div>
            </div>

            {{-- Advanced Filters Toggle --}}
            <div>
                <button type="button" class="text-sm text-[var(--text-secondary)] hover:text-[var(--text-primary)]" @click="advancedOpen = !advancedOpen">
                    <i class="bi bi-sliders mr-1"></i>Filter Lanjutan
                </button>

                <div x-show="advancedOpen" x-transition class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-3">
                        <label class="text-sm font-medium text-[var(--text-primary)]">Kategori</label>
                        <select name="category_id" class="glass-input mt-2">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-3">
                        <label class="text-sm font-medium text-[var(--text-primary)]">Dari Tanggal</label>
                        <input type="date" class="glass-input mt-2" name="date_from" value="{{ request('date_from') }}">
                    </div>

                    <div class="lg:col-span-3">
                        <label class="text-sm font-medium text-[var(--text-primary)]">Sampai Tanggal</label>
                        <input type="date" class="glass-input mt-2" name="date_to" value="{{ request('date_to') }}">
                    </div>

                    <div class="lg:col-span-3">
                        <label class="text-sm font-medium text-[var(--text-primary)]">Status Kedaluwarsa</label>
                        <select name="expiring_days" class="glass-input mt-2">
                            <option value="">Semua</option>
                            <option value="30" {{ request('expiring_days') == '30' ? 'selected' : '' }}>Kedaluwarsa 30 hari</option>
                            <option value="60" {{ request('expiring_days') == '60' ? 'selected' : '' }}>Kedaluwarsa 60 hari</option>
                            <option value="90" {{ request('expiring_days') == '90' ? 'selected' : '' }}>Kedaluwarsa 90 hari</option>
                        </select>
                    </div>

                    <div class="lg:col-span-3 flex items-end">
                        <label class="inline-flex items-center gap-2 text-sm text-[var(--text-secondary)]">
                            <input class="h-4 w-4 rounded border-[var(--surface-glass-border)] text-primary-500 focus:ring-primary-500" type="checkbox" name="expired" value="1"
                                   {{ request('expired') ? 'checked' : '' }}>
                            Sudah Kedaluwarsa
                        </label>
                    </div>
                </div>
            </div>
        </form>
    </x-glass-card>

    {{-- Active Filter Chips --}}
    @if(request()->hasAny(['search', 'type_id', 'status', 'unit_id', 'category_id', 'date_from', 'date_to', 'expiring_days', 'expired']))
    <div class="mb-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs text-[var(--text-tertiary)]">Filter aktif:</span>
            
            @if(request('search'))
                <a href="{{ request()->fullUrlWithoutQuery('search') }}" class="inline-flex items-center gap-2 rounded-full border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] px-3 py-1 text-xs text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                    Pencarian: "{{ Str::limit(request('search'), 20) }}"
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
            
            @if(request('type_id'))
                <a href="{{ request()->fullUrlWithoutQuery('type_id') }}" class="inline-flex items-center gap-2 rounded-full border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] px-3 py-1 text-xs text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                    Jenis: {{ $types->firstWhere('id', request('type_id'))?->name ?? 'N/A' }}
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
            
            @if(request('status'))
                <a href="{{ request()->fullUrlWithoutQuery('status') }}" class="inline-flex items-center gap-2 rounded-full border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] px-3 py-1 text-xs text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                    Status: {{ $statuses[request('status')] ?? request('status') }}
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
            
            @if(request('unit_id'))
                <a href="{{ request()->fullUrlWithoutQuery('unit_id') }}" class="inline-flex items-center gap-2 rounded-full border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] px-3 py-1 text-xs text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                    Unit: {{ $units->firstWhere('id', request('unit_id'))?->name ?? 'N/A' }}
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
            
            @if(request('category_id'))
                <a href="{{ request()->fullUrlWithoutQuery('category_id') }}" class="inline-flex items-center gap-2 rounded-full border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] px-3 py-1 text-xs text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                    Kategori: {{ $categories->firstWhere('id', request('category_id'))?->name ?? 'N/A' }}
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
            
            @if(request('date_from'))
                <a href="{{ request()->fullUrlWithoutQuery('date_from') }}" class="inline-flex items-center gap-2 rounded-full border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] px-3 py-1 text-xs text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                    Dari: {{ \Carbon\Carbon::parse(request('date_from'))->format('d M Y') }}
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
            
            @if(request('date_to'))
                <a href="{{ request()->fullUrlWithoutQuery('date_to') }}" class="inline-flex items-center gap-2 rounded-full border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] px-3 py-1 text-xs text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                    Sampai: {{ \Carbon\Carbon::parse(request('date_to'))->format('d M Y') }}
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
            
            @if(request('expiring_days'))
                <a href="{{ request()->fullUrlWithoutQuery('expiring_days') }}" class="inline-flex items-center gap-2 rounded-full border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] px-3 py-1 text-xs text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                    Kedaluwarsa: {{ request('expiring_days') }} hari
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
            
            @if(request('expired'))
                <a href="{{ request()->fullUrlWithoutQuery('expired') }}" class="inline-flex items-center gap-2 rounded-full border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] px-3 py-1 text-xs text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                    Sudah Kedaluwarsa
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
            
            <a href="{{ route('documents.index') }}" class="inline-flex items-center gap-2 rounded-full border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] px-3 py-1 text-xs text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                <i class="bi bi-x-circle"></i>Hapus Semua Filter
            </a>
        </div>
    </div>
    @endif

    {{-- Status Legend --}}
    <div class="mb-4" x-data="{ showLegend: false }">
        <x-button type="button" size="sm" variant="secondary" @click="showLegend = !showLegend">
            <i class="bi bi-question-circle mr-2"></i>
            <span>Keterangan Warna Status</span>
            <i class="bi bi-chevron-down ml-2 transition-transform" :class="{ 'rotate-180': showLegend }"></i>
        </x-button>

        <div x-show="showLegend" x-transition x-cloak class="mt-4">
            <x-glass-card :hover="false" class="p-6">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-[var(--text-tertiary)]">Status Dokumen</h3>
                        <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-4">
                            <div class="flex items-center gap-2 text-xs text-[var(--text-tertiary)]">
                                <x-badge type="default" size="sm">Draft</x-badge>
                                <span>Dokumen masih dalam tahap penyusunan</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-[var(--text-tertiary)]">
                                <x-badge type="info" size="sm">Menunggu Review</x-badge>
                                <span>Dokumen sedang direview</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-[var(--text-tertiary)]">
                                <x-badge type="warning" size="sm">Menunggu Approval</x-badge>
                                <span>Menunggu persetujuan</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-[var(--text-tertiary)]">
                                <x-badge type="success" size="sm">Dipublikasikan</x-badge>
                                <span>Dokumen aktif dan berlaku</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-[var(--text-tertiary)]">Status Masa Berlaku</h3>
                        <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                            <div class="flex items-center gap-2 text-xs text-[var(--text-tertiary)]">
                                <x-badge type="success" size="sm">Aktif</x-badge>
                                <span>&gt; 6 bulan</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-[var(--text-tertiary)]">
                                <x-badge type="attention" size="sm">Perhatian</x-badge>
                                <span>≤ 6 bulan</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-[var(--text-tertiary)]">
                                <x-badge type="warning" size="sm">Peringatan</x-badge>
                                <span>≤ 3 bulan</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-[var(--text-tertiary)]">
                                <x-badge type="critical" size="sm">Kritis</x-badge>
                                <span>≤ 1 bulan</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-[var(--text-tertiary)]">
                                <x-badge type="expired" size="sm">Kedaluwarsa</x-badge>
                                <span>Sudah lewat</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-[var(--text-tertiary)]">
                                <x-badge type="default" size="sm">Permanen</x-badge>
                                <span>Tanpa batas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </x-glass-card>
        </div>
    </div>

    {{-- Documents Table View --}}
    <div x-show="viewMode === 'table'" x-cloak>
        <x-table>
            <x-slot name="header">
                <th class="w-10">
                    <input type="checkbox"
                           @change="toggleSelectAll($event.target.checked)"
                           :checked="isAllSelected"
                           :indeterminate.prop="isIndeterminate">
                </th>
                <x-table-header sortable sortKey="document_number">Nomor</x-table-header>
                <x-table-header sortable sortKey="title">Judul</x-table-header>
                <th>Jenis</th>
                <th>Unit</th>
                <x-table-header sortable sortKey="document_date">Tanggal</x-table-header>
                <th>Status</th>
                <x-table-header sortable sortKey="updated_at">Terakhir Update</x-table-header>
                <th class="text-right">Aksi</th>
            </x-slot>

            @forelse($documents as $document)
                @php
                    $expiryInfo = app(\App\Services\DocumentStatusService::class)->getExpiryInfo($document);
                    $expiryLabelMap = [
                        \App\Services\DocumentStatusService::STATUS_ATTENTION => 'Perhatian',
                        \App\Services\DocumentStatusService::STATUS_WARNING => 'Peringatan',
                        \App\Services\DocumentStatusService::STATUS_CRITICAL => 'Kritis',
                        \App\Services\DocumentStatusService::STATUS_EXPIRED => 'Kedaluwarsa',
                    ];
                    $statusTypes = [
                        'draft' => 'default',
                        'pending_review' => 'info',
                        'pending_approval' => 'warning',
                        'approved' => 'active',
                        'published' => 'success',
                        'expired' => 'expired',
                        'archived' => 'default',
                        'rejected' => 'expired',
                    ];
                @endphp
                <tr :class="{ 'row-selected': selectedIds.includes({{ $document->id }}) }">
                    <td>
                        <input type="checkbox"
                               value="{{ $document->id }}"
                               @change="toggleSelect({{ $document->id }})"
                               :checked="selectedIds.includes({{ $document->id }})">
                    </td>
                    <td class="text-xs font-mono text-[var(--text-tertiary)]">
                        @if(request('search'))
                            {!! preg_replace('/(' . preg_quote(request('search'), '/') . ')/i', '<mark class="rounded bg-amber-200/50 px-1 text-amber-900 dark:bg-amber-400/20 dark:text-amber-200">$1</mark>', e($document->document_number)) !!}
                        @else
                            {{ $document->document_number }}
                        @endif
                    </td>
                    <td>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('documents.show', $document) }}" class="text-sm font-semibold text-[var(--text-primary)] hover:text-primary-500">
                                    @if(request('search'))
                                        {!! preg_replace('/(' . preg_quote(request('search'), '/') . ')/i', '<mark class="rounded bg-amber-200/50 px-1 text-amber-900 dark:bg-amber-400/20 dark:text-amber-200">$1</mark>', e(Str::limit($document->title, 50))) !!}
                                    @else
                                        {{ Str::limit($document->title, 50) }}
                                    @endif
                                </a>
                                @if(isset($expiryLabelMap[$expiryInfo['status']]))
                                    <x-badge :type="$expiryInfo['status']" size="sm">
                                        {{ $expiryLabelMap[$expiryInfo['status']] }}
                                    </x-badge>
                                @endif
                            </div>
                            @if($document->category)
                                <div class="text-xs text-[var(--text-tertiary)]">{{ $document->category->name }}</div>
                            @endif
                        </div>
                    </td>
                    <td>
                        <x-badge type="default" size="sm">{{ $document->type->name ?? '-' }}</x-badge>
                    </td>
                    <td class="text-sm text-[var(--text-primary)]">{{ $document->unit->name ?? '-' }}</td>
                    <td class="text-sm text-[var(--text-primary)]">{{ $document->document_date?->format('d/m/Y') }}</td>
                    <td>
                        <x-badge :type="$statusTypes[$document->status] ?? 'default'" size="sm">
                            {{ $statuses[$document->status] ?? $document->status }}
                        </x-badge>
                    </td>
                    <td class="text-xs text-[var(--text-tertiary)]">{{ $document->updated_at->diffForHumans() }}</td>
                    <td class="text-right">
                        <x-dropdown align="right" width="56">
                            <x-slot name="trigger">
                                <button class="btn-ghost px-2 py-2 rounded-lg" type="button" aria-label="Aksi">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                            </x-slot>

                            <x-dropdown-item href="{{ route('documents.show', $document) }}">
                                <i class="bi bi-eye text-[var(--text-tertiary)]"></i>
                                <span>Lihat</span>
                            </x-dropdown-item>
                            @if($document->currentVersion)
                                <x-dropdown-item href="{{ route('documents.download', $document) }}">
                                    <i class="bi bi-download text-[var(--text-tertiary)]"></i>
                                    <span>Download</span>
                                </x-dropdown-item>
                            @endif
                            @can('documents.edit')
                                @if($document->isEditable())
                                    <x-dropdown-item href="{{ route('documents.edit', $document) }}">
                                        <i class="bi bi-pencil text-[var(--text-tertiary)]"></i>
                                        <span>Edit</span>
                                    </x-dropdown-item>
                                @endif
                            @endcan
                            @can('documents.delete')
                                @if($document->status === 'draft')
                                    <div class="border-t border-[var(--surface-glass-border)] my-1"></div>
                                    <form action="{{ route('documents.destroy', $document) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus dokumen ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-dropdown-item type="submit" class="text-red-500 hover:text-red-600">
                                            <i class="bi bi-trash"></i>
                                            <span>Hapus</span>
                                        </x-dropdown-item>
                                    </form>
                                @endif
                            @endcan
                        </x-dropdown>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="py-10 text-center">
                        <div class="space-y-3 text-[var(--text-tertiary)]">
                            <i class="bi bi-file-earmark-text text-3xl opacity-40"></i>
                            <p class="text-sm">Tidak ada dokumen ditemukan.</p>
                            @can('documents.create')
                                <x-button href="{{ route('documents.create') }}" size="sm">
                                    <i class="bi bi-plus-lg"></i>
                                    Tambah Dokumen Pertama
                                </x-button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforelse

            @if($documents->hasPages())
                <x-slot name="pagination">
                    {{ $documents->withQueryString()->links() }}
                </x-slot>
            @endif
        </x-table>
    </div>

    {{-- Documents Card View --}}
    <div x-show="viewMode === 'card'" x-cloak>
        @if($documents->count() > 0)
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($documents as $document)
                    @php
                        $expiryInfo = app(\App\Services\DocumentStatusService::class)->getExpiryInfo($document);
                        $expiryLabelMap = [
                            \App\Services\DocumentStatusService::STATUS_ATTENTION => 'Perhatian',
                            \App\Services\DocumentStatusService::STATUS_WARNING => 'Peringatan',
                            \App\Services\DocumentStatusService::STATUS_CRITICAL => 'Kritis',
                            \App\Services\DocumentStatusService::STATUS_EXPIRED => 'Kedaluwarsa',
                        ];
                        $expiryBorderMap = [
                            \App\Services\DocumentStatusService::STATUS_ATTENTION => 'border-blue-400/40',
                            \App\Services\DocumentStatusService::STATUS_WARNING => 'border-emerald-400/40',
                            \App\Services\DocumentStatusService::STATUS_CRITICAL => 'border-amber-400/40',
                            \App\Services\DocumentStatusService::STATUS_EXPIRED => 'border-red-500/40',
                        ];
                        $expiryTextMap = [
                            \App\Services\DocumentStatusService::STATUS_ATTENTION => 'text-blue-500',
                            \App\Services\DocumentStatusService::STATUS_WARNING => 'text-emerald-500',
                            \App\Services\DocumentStatusService::STATUS_CRITICAL => 'text-amber-500',
                            \App\Services\DocumentStatusService::STATUS_EXPIRED => 'text-red-500',
                        ];
                        $statusTypes = [
                            'draft' => 'default',
                            'pending_review' => 'info',
                            'pending_approval' => 'warning',
                            'approved' => 'active',
                            'published' => 'success',
                            'expired' => 'expired',
                            'archived' => 'default',
                            'rejected' => 'expired',
                        ];
                        $expiryBorderClass = $expiryBorderMap[$expiryInfo['status']] ?? '';
                        $expiryTextClass = $expiryTextMap[$expiryInfo['status']] ?? 'text-primary-500';
                    @endphp

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
                            <div class="swipe-content glass-card p-4 transition-all hover:shadow-lg {{ $expiryBorderClass }}"
                             :class="{ 'swiping': isSwiping }"
                             :style="{ transform: `translateX(${swipeOffset}px)` }">
                            <div class="flex h-full flex-col">
                                <div class="space-y-3">
                                    {{-- Status & Expiry Badges --}}
                                    <div class="flex flex-wrap gap-2">
                                        <x-badge :type="$statusTypes[$document->status] ?? 'default'" size="sm">
                                            {{ $statuses[$document->status] ?? $document->status }}
                                        </x-badge>
                                        @if(isset($expiryLabelMap[$expiryInfo['status']]))
                                            <x-badge :type="$expiryInfo['status']" size="sm">
                                                {{ $expiryLabelMap[$expiryInfo['status']] }}
                                            </x-badge>
                                        @endif
                                    </div>

                                    {{-- Document Number --}}
                                    <p class="text-xs font-mono text-[var(--text-tertiary)]">
                                        @if(request('search'))
                                            {!! preg_replace('/(' . preg_quote(request('search'), '/') . ')/i', '<mark class="rounded bg-amber-200/50 px-1 text-amber-900 dark:bg-amber-400/20 dark:text-amber-200">$1</mark>', e($document->document_number)) !!}
                                        @else
                                            {{ $document->document_number }}
                                        @endif
                                    </p>

                                    {{-- Title --}}
                                    <h3 class="text-sm font-semibold text-[var(--text-primary)]">
                                        <a href="{{ route('documents.show', $document) }}" class="hover:text-primary-500">
                                            @if(request('search'))
                                                {!! preg_replace('/(' . preg_quote(request('search'), '/') . ')/i', '<mark class="rounded bg-amber-200/50 px-1 text-amber-900 dark:bg-amber-400/20 dark:text-amber-200">$1</mark>', e(Str::limit($document->title, 60))) !!}
                                            @else
                                                {{ Str::limit($document->title, 60) }}
                                            @endif
                                        </a>
                                    </h3>

                                    {{-- Metadata --}}
                                    <div class="space-y-1 text-xs text-[var(--text-tertiary)]">
                                        <div class="flex items-center gap-2">
                                            <i class="bi bi-folder2 text-primary-500"></i>
                                            <span>{{ $document->type->name ?? '-' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="bi bi-building text-primary-500"></i>
                                            <span>{{ Str::limit($document->unit->name ?? '-', 25) }}</span>
                                        </div>
                                        @if($document->expiry_date)
                                            <div class="flex items-center gap-2">
                                                <i class="bi bi-calendar-event {{ $expiryTextClass }}"></i>
                                                <span class="{{ $expiryTextClass }}">
                                                    {{ $document->expiry_date->format('d M Y') }}
                                                    ({{ $expiryInfo['days_text'] }})
                                                </span>
                                            </div>
                                        @else
                                            <div class="flex items-center gap-2 text-emerald-500">
                                                <i class="bi bi-infinity"></i>
                                                <span>Permanen</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Card Footer --}}
                                <div class="mt-4 flex items-center justify-between border-t border-[var(--surface-glass-border)] pt-3 text-xs text-[var(--text-tertiary)]">
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-clock"></i>
                                        <span>{{ $document->updated_at->diffForHumans() }}</span>
                                    </div>
                                    <x-dropdown align="right" width="56">
                                        <x-slot name="trigger">
                                            <button class="btn-ghost px-2 py-2 rounded-lg" type="button" aria-label="Aksi" @click.stop>
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                        </x-slot>
                                        <x-dropdown-item href="{{ route('documents.show', $document) }}">
                                            <i class="bi bi-eye text-[var(--text-tertiary)]"></i>
                                            <span>Lihat</span>
                                        </x-dropdown-item>
                                        @if($document->currentVersion)
                                            <x-dropdown-item href="{{ route('documents.download', $document) }}">
                                                <i class="bi bi-download text-[var(--text-tertiary)]"></i>
                                                <span>Download</span>
                                            </x-dropdown-item>
                                        @endif
                                        @can('documents.edit')
                                            @if($document->isEditable())
                                                <x-dropdown-item href="{{ route('documents.edit', $document) }}">
                                                    <i class="bi bi-pencil text-[var(--text-tertiary)]"></i>
                                                    <span>Edit</span>
                                                </x-dropdown-item>
                                            @endif
                                        @endcan
                                    </x-dropdown>
                                </div>
                            </div>
                        </div>{{-- End swipe-content --}}
                    </div>{{-- End swipe-container --}}
                @endforeach
            </div>

            @if($documents->hasPages())
                <div class="mt-4">
                    {{ $documents->withQueryString()->links() }}
                </div>
            @endif
        @else
            <x-glass-card :hover="false" class="py-10 text-center">
                <div class="space-y-3 text-[var(--text-tertiary)]">
                    <i class="bi bi-file-earmark-text text-3xl opacity-40"></i>
                    <p class="text-sm">Tidak ada dokumen ditemukan.</p>
                    @can('documents.create')
                        <x-button href="{{ route('documents.create') }}" size="sm">
                            <i class="bi bi-plus-lg"></i>
                            Tambah Dokumen Pertama
                        </x-button>
                    @endcan
                </div>
            </x-glass-card>
        @endif
    </div>

    {{-- Bulk Delete Confirmation Modal --}}
    <div x-show="showDeleteModal" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="modal-backdrop"
         @click.self="showDeleteModal = false"
         role="dialog"
         aria-modal="true">
        <div class="modal-content max-w-lg" @click.stop>
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-2 text-red-500">
                    <i class="bi bi-exclamation-triangle"></i>
                    <h3 class="text-lg font-semibold">Konfirmasi Hapus</h3>
                </div>
                <button type="button" class="p-2 rounded-lg text-[var(--text-tertiary)] hover:text-[var(--text-primary)] hover:bg-[var(--surface-glass)]" @click="showDeleteModal = false" aria-label="Tutup">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="mt-4 space-y-2 text-sm text-[var(--text-secondary)]">
                <p>Anda akan menghapus <strong class="text-[var(--text-primary)]" x-text="selectedIds.length"></strong> dokumen.</p>
                <p class="text-xs text-[var(--text-tertiary)]">
                    Dokumen yang dihapus dapat dipulihkan dari arsip dalam 30 hari.
                </p>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-2 border-t border-[var(--surface-glass-border)] pt-4 sm:flex-row sm:justify-end">
                <x-button type="button" variant="secondary" @click="showDeleteModal = false">Batal</x-button>
                <x-button type="button" variant="danger" @click="executeBulkDelete()" x-bind:disabled="isDeleting">
                    <span x-show="!isDeleting" class="inline-flex items-center gap-2">
                        <i class="bi bi-trash"></i>
                        Hapus <span x-text="selectedIds.length"></span> Dokumen
                    </span>
                    <span x-show="isDeleting" class="inline-flex items-center gap-2">
                        <span class="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
                        Menghapus...
                    </span>
                </x-button>
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
            toast.className = 'position-fixed glass-toast';
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
