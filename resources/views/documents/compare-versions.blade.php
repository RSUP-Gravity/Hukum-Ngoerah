@extends('layouts.app')

@section('title', 'Bandingkan Versi - ' . Str::limit($document->title, 30))

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Dokumen', 'url' => route('documents.index')],
        ['label' => Str::limit($document->title, 25), 'url' => route('documents.show', $document)],
        ['label' => 'Bandingkan Versi']
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Bandingkan Versi</h1>
            <p class="text-muted mb-0">{{ $document->document_number }} • {{ $document->title }}</p>
        </div>
        <a href="{{ route('documents.show', $document) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>

    {{-- Version Comparison Header --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="glass-card border-start border-warning border-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="badge bg-warning text-dark mb-2">Versi Lama</span>
                            <h5 class="mb-1">Versi {{ $version1->version_number }}</h5>
                            <p class="text-muted mb-0 small">
                                <i class="bi bi-calendar me-1"></i>{{ $version1->created_at->format('d M Y, H:i') }}
                            </p>
                            <p class="text-muted mb-0 small">
                                <i class="bi bi-person me-1"></i>{{ $version1->uploader->name ?? 'System' }}
                            </p>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('documents.download', [$document, $version1]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download me-1"></i>Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="glass-card border-start border-success border-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="badge bg-success mb-2">Versi Baru</span>
                            <h5 class="mb-1">Versi {{ $version2->version_number }}</h5>
                            <p class="text-muted mb-0 small">
                                <i class="bi bi-calendar me-1"></i>{{ $version2->created_at->format('d M Y, H:i') }}
                            </p>
                            <p class="text-muted mb-0 small">
                                <i class="bi bi-person me-1"></i>{{ $version2->uploader->name ?? 'System' }}
                            </p>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('documents.download', [$document, $version2]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download me-1"></i>Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Changes Summary --}}
        <div class="col-lg-8">
            <div class="glass-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-arrow-left-right me-2"></i>Ringkasan Perubahan
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($metadataDiff) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th style="width: 30%">Atribut</th>
                                        <th style="width: 35%">Versi {{ $version1->version_number }}</th>
                                        <th style="width: 35%">Versi {{ $version2->version_number }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($metadataDiff['file_size']))
                                    <tr>
                                        <td><strong>Ukuran File</strong></td>
                                        <td class="text-warning-emphasis bg-warning-subtle">
                                            {{ $metadataDiff['file_size']['old'] }}
                                        </td>
                                        <td class="text-success-emphasis bg-success-subtle">
                                            {{ $metadataDiff['file_size']['new'] }}
                                            @if($metadataDiff['file_size']['change'] === 'increased')
                                                <i class="bi bi-arrow-up text-success"></i>
                                            @else
                                                <i class="bi bi-arrow-down text-danger"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                    
                                    @if(isset($metadataDiff['filename']))
                                    <tr>
                                        <td><strong>Nama File</strong></td>
                                        <td class="text-warning-emphasis bg-warning-subtle">
                                            {{ $metadataDiff['filename']['old'] }}
                                        </td>
                                        <td class="text-success-emphasis bg-success-subtle">
                                            {{ $metadataDiff['filename']['new'] }}
                                        </td>
                                    </tr>
                                    @endif
                                    
                                    @if(isset($metadataDiff['file_content']))
                                    <tr>
                                        <td><strong>Konten File</strong></td>
                                        <td colspan="2" class="text-center text-info">
                                            <i class="bi bi-file-diff me-1"></i>{{ $metadataDiff['file_content']['message'] }}
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-check-circle fs-1 text-success"></i>
                            <p class="mt-2 mb-0">Tidak ada perbedaan metadata yang terdeteksi.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- File Comparison --}}
            <div class="glass-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-file-earmark-diff me-2"></i>Perbandingan File
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="border rounded p-3 bg-warning-subtle">
                                <div class="d-flex align-items-center mb-2">
                                    @php
                                        $icon1 = match($version1->file_extension ?? 'pdf') {
                                            'pdf' => 'bi-file-earmark-pdf text-danger',
                                            'doc', 'docx' => 'bi-file-earmark-word text-primary',
                                            default => 'bi-file-earmark text-secondary',
                                        };
                                    @endphp
                                    <i class="bi {{ $icon1 }} fs-4 me-2"></i>
                                    <div>
                                        <div class="fw-medium">{{ $version1->original_filename }}</div>
                                        <small class="text-muted">{{ $version1->file_size_formatted ?? number_format($version1->file_size / 1024, 0) . ' KB' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 bg-success-subtle">
                                <div class="d-flex align-items-center mb-2">
                                    @php
                                        $icon2 = match($version2->file_extension ?? 'pdf') {
                                            'pdf' => 'bi-file-earmark-pdf text-danger',
                                            'doc', 'docx' => 'bi-file-earmark-word text-primary',
                                            default => 'bi-file-earmark text-secondary',
                                        };
                                    @endphp
                                    <i class="bi {{ $icon2 }} fs-4 me-2"></i>
                                    <div>
                                        <div class="fw-medium">{{ $version2->original_filename }}</div>
                                        <small class="text-muted">{{ $version2->file_size_formatted ?? number_format($version2->file_size / 1024, 0) . ' KB' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Untuk perbandingan konten detail, silakan download kedua file dan gunakan tool pembanding dokumen eksternal.
                    </div>
                </div>
            </div>

            {{-- Change Notes --}}
            @if($version2->change_notes)
            <div class="glass-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-sticky me-2"></i>Catatan Perubahan
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $version2->change_notes }}</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Activity Between Versions --}}
            <div class="glass-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2"></i>Aktivitas
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                        @forelse($history as $item)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="fw-medium small">{{ $item->action_label }}</div>
                                    @if($item->description)
                                        <small class="text-muted">{{ $item->description }}</small>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <div class="small">{{ $item->performer->name ?? 'System' }}</div>
                                    <small class="text-muted">{{ $item->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item text-center text-muted py-4">
                            Tidak ada aktivitas antara dua versi ini
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            @can('documents.edit')
            <div class="glass-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Aksi</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(auth()->user()->isAdmin() && !$version1->is_current)
                        <form action="{{ route('documents.restore-version', [$document, $version1]) }}" method="POST" 
                              onsubmit="return confirm('Pulihkan versi {{ $version1->version_number }}? Ini akan membuat versi baru berdasarkan versi tersebut.');">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning w-100">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Pulihkan Versi {{ $version1->version_number }}
                            </button>
                        </form>
                        @endif
                        
                        <a href="{{ route('documents.show', $document) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Dokumen
                        </a>
                    </div>
                </div>
            </div>
            @endcan
        </div>
    </div>
</div>
@endsection
