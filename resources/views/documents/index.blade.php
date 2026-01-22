@extends('layouts.app')

@section('title', 'Daftar Dokumen')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Dokumen']
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Dokumen</h1>
            <p class="text-muted mb-0">Kelola dokumen hukum organisasi</p>
        </div>
        @can('documents.create')
        <a href="{{ route('documents.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Tambah Dokumen
        </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="glass-card mb-4">
        <div class="card-body">
            <form action="{{ route('documents.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Pencarian</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" name="search" 
                                   value="{{ request('search') }}" placeholder="Cari judul, nomor, atau deskripsi...">
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
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
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

    {{-- Documents Table --}}
    <div class="glass-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
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
                        <tr>
                            <td>
                                <span class="font-monospace">{{ $document->document_number }}</span>
                            </td>
                            <td>
                                <div>
                                    <a href="{{ route('documents.show', $document) }}" class="text-decoration-none fw-medium">
                                        {{ Str::limit($document->title, 50) }}
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
                            <td colspan="8" class="text-center py-5">
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
</div>
@endsection
