@extends('layouts.app')

@section('title', $document->title)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Dokumen', 'url' => route('documents.index')],
        ['label' => Str::limit($document->title, 30)]
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Main Content --}}
        <div class="col-lg-8">
            {{-- Document Header --}}
            <div class="glass-card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
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
                                    $statusLabels = App\Models\Document::STATUSES;
                                @endphp
                                <span class="badge bg-{{ $statusColors[$document->status] ?? 'secondary' }} fs-6">
                                    {{ $statusLabels[$document->status] ?? $document->status }}
                                </span>
                                @if($document->isExpired())
                                    <span class="badge bg-danger">Kedaluwarsa</span>
                                @elseif($document->expiry_date && $document->expiry_date->diffInDays(now()) <= 30)
                                    <span class="badge bg-warning text-dark">Segera Kedaluwarsa</span>
                                @endif
                                <span class="badge bg-light text-dark">{{ $document->type->name ?? '-' }}</span>
                            </div>
                            <h1 class="h3 mb-2">{{ $document->title }}</h1>
                            <p class="text-muted mb-0">
                                <i class="bi bi-hash me-1"></i>{{ $document->document_number }}
                                <span class="mx-2">•</span>
                                <i class="bi bi-calendar me-1"></i>{{ $document->document_date?->format('d F Y') }}
                                @if($document->category)
                                <span class="mx-2">•</span>
                                <i class="bi bi-folder me-1"></i>{{ $document->category->name }}
                                @endif
                            </p>
                        </div>
                        
                        {{-- Action Buttons --}}
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if($document->currentVersion)
                                <li>
                                    <a class="dropdown-item" href="{{ route('documents.download', $document) }}">
                                        <i class="bi bi-download me-2"></i>Download
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('documents.print', $document) }}" target="_blank">
                                        <i class="bi bi-printer me-2"></i>Print
                                    </a>
                                </li>
                                @endif
                                @can('documents.edit')
                                @if($document->isEditable())
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('documents.edit', $document) }}">
                                        <i class="bi bi-pencil me-2"></i>Edit
                                    </a>
                                </li>
                                @endif
                                @endcan
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Document Content --}}
            <div class="glass-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Dokumen</h5>
                </div>
                <div class="card-body">
                    @if($document->parties)
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Pihak-pihak Terkait</h6>
                        <p class="mb-0">{{ $document->parties }}</p>
                    </div>
                    @endif
                    
                    @if($document->description)
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Deskripsi</h6>
                        <p class="mb-0">{!! nl2br(e($document->description)) !!}</p>
                    </div>
                    @endif
                    
                    @if($document->notes)
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Catatan Internal</h6>
                        <div class="alert alert-light mb-0">
                            {!! nl2br(e($document->notes)) !!}
                        </div>
                    </div>
                    @endif
                    
                    @if($document->rejection_reason)
                    <div class="mb-4">
                        <h6 class="text-danger mb-2">Alasan Penolakan</h6>
                        <div class="alert alert-danger mb-0">
                            {!! nl2br(e($document->rejection_reason)) !!}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            {{-- Versions --}}
            <div class="glass-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Versi Dokumen</h5>
                    @can('documents.edit')
                    @if($document->isEditable())
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#uploadVersionModal">
                        <i class="bi bi-upload me-1"></i>Unggah Versi Baru
                    </button>
                    @endif
                    @endcan
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($document->versions as $index => $version)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        @php
                                            $iconClass = match($version->file_extension) {
                                                'pdf' => 'bi-file-earmark-pdf text-danger',
                                                'doc', 'docx' => 'bi-file-earmark-word text-primary',
                                                default => 'bi-file-earmark text-secondary',
                                            };
                                        @endphp
                                        <i class="bi {{ $iconClass }} fs-3"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium">
                                            Versi {{ $version->version_number }}
                                            @if($version->version_number === $document->current_version)
                                                <span class="badge bg-success ms-1">Terbaru</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">
                                            {{ $version->original_filename }} • 
                                            {{ number_format($version->file_size / 1024, 0) }} KB •
                                            {{ $version->created_at->format('d/m/Y H:i') }}
                                        </small>
                                        @if($version->change_notes)
                                            <div class="small text-muted mt-1">{{ $version->change_notes }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex gap-1">
                                    {{-- Compare with previous version --}}
                                    @if($document->versions->count() > 1 && $version->version_number > 1)
                                        @php
                                            $prevVersion = $document->versions->firstWhere('version_number', $version->version_number - 1);
                                        @endphp
                                        @if($prevVersion)
                                        <a href="{{ route('documents.compare-versions', [$document, $prevVersion, $version]) }}" 
                                           class="btn btn-sm btn-outline-info" title="Bandingkan dengan versi sebelumnya">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </a>
                                        @endif
                                    @endif
                                    
                                    {{-- Restore version (Admin only, not current) --}}
                                    @if(auth()->user()->isAdmin() && $version->version_number !== $document->current_version)
                                    <form action="{{ route('documents.restore-version', [$document, $version]) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Pulihkan versi {{ $version->version_number }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Pulihkan versi ini">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>
                                    @endif
                                    
                                    <a href="{{ route('documents.download', [$document, $version]) }}" class="btn btn-sm btn-outline-secondary" title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item text-center text-muted py-4">
                            Belum ada file yang diunggah
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            {{-- History --}}
            <div class="glass-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Riwayat Aktivitas</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                        @forelse($document->history as $history)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="fw-medium">{{ App\Models\DocumentHistory::ACTIONS[$history->action] ?? $history->action }}</div>
                                    @if($history->description)
                                        <small class="text-muted">{{ $history->description }}</small>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <div class="small">{{ $history->performer->name ?? 'System' }}</div>
                                    <small class="text-muted">{{ $history->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item text-center text-muted py-4">
                            Belum ada riwayat aktivitas
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Workflow Actions --}}
            @if($document->status !== 'archived')
            <div class="glass-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Aksi</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        {{-- Submit for Review --}}
                        @if($document->canSubmitForReview())
                        @can('documents.create')
                        <form action="{{ route('documents.submit-review', $document) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-send me-2"></i>Ajukan untuk Review
                            </button>
                        </form>
                        @endcan
                        @endif
                        
                        {{-- Submit for Approval --}}
                        @if($document->status === 'pending_review')
                        @can('documents.approve')
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitApprovalModal">
                            <i class="bi bi-check-circle me-2"></i>Ajukan untuk Approval
                        </button>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle me-2"></i>Tolak
                        </button>
                        @endcan
                        @endif
                        
                        {{-- Approve/Reject --}}
                        @if($document->status === 'pending_approval')
                        @php
                            $pendingApproval = $document->approvals()->where('approver_id', auth()->id())->where('status', 'pending')->first();
                        @endphp
                        @if($pendingApproval)
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i class="bi bi-check-lg me-2"></i>Setujui
                        </button>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-lg me-2"></i>Tolak
                        </button>
                        @endif
                        @endif
                        
                        {{-- Publish --}}
                        @if($document->status === 'approved')
                        @can('documents.publish')
                        <form action="{{ route('documents.publish', $document) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-globe me-2"></i>Publikasikan
                            </button>
                        </form>
                        @endcan
                        @endif
                        
                        {{-- Archive --}}
                        @if(in_array($document->status, ['published', 'expired']))
                        @can('documents.archive')
                        <form action="{{ route('documents.archive', $document) }}" method="POST" onsubmit="return confirm('Yakin ingin mengarsipkan dokumen ini?')">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-archive me-2"></i>Arsipkan
                            </button>
                        </form>
                        @endcan
                        @endif
                    </div>
                </div>
            </div>
            @endif
            
            {{-- Approval Status --}}
            @if($document->approvals->isNotEmpty())
            <div class="glass-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Status Approval</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($document->approvals as $approval)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-medium">{{ $approval->approver->name ?? '-' }}</div>
                                <small class="text-muted">Level {{ $approval->sequence }}</small>
                            </div>
                            @php
                                $approvalStatusClass = match($approval->status) {
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    default => 'warning',
                                };
                                $approvalStatusLabel = match($approval->status) {
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak',
                                    default => 'Menunggu',
                                };
                            @endphp
                            <span class="badge bg-{{ $approvalStatusClass }}">{{ $approvalStatusLabel }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
            
            {{-- Document Details --}}
            <div class="glass-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Detail</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-muted">Unit</dt>
                        <dd class="col-sm-7">{{ $document->unit->name ?? '-' }}</dd>
                        
                        <dt class="col-sm-5 text-muted">Kerahasiaan</dt>
                        <dd class="col-sm-7">
                            @php
                                $confColors = ['public' => 'success', 'internal' => 'info', 'confidential' => 'warning', 'secret' => 'danger'];
                                $confLabels = App\Models\Document::CONFIDENTIALITIES;
                            @endphp
                            <span class="badge bg-{{ $confColors[$document->confidentiality] ?? 'secondary' }}">
                                {{ $confLabels[$document->confidentiality] ?? $document->confidentiality }}
                            </span>
                        </dd>
                        
                        @if($document->effective_date)
                        <dt class="col-sm-5 text-muted">Berlaku</dt>
                        <dd class="col-sm-7">{{ $document->effective_date->format('d/m/Y') }}</dd>
                        @endif
                        
                        @if($document->expiry_date)
                        <dt class="col-sm-5 text-muted">Kedaluwarsa</dt>
                        <dd class="col-sm-7">
                            {{ $document->expiry_date->format('d/m/Y') }}
                            @if($document->isExpired())
                                <span class="badge bg-danger ms-1">Sudah</span>
                            @endif
                        </dd>
                        @endif
                        
                        <dt class="col-sm-5 text-muted">Versi</dt>
                        <dd class="col-sm-7">{{ $document->current_version }}</dd>
                        
                        <dt class="col-sm-5 text-muted">Jumlah Unduhan</dt>
                        <dd class="col-sm-7">
                            <span class="d-inline-flex align-items-center">
                                <i class="bi bi-download me-2 text-primary"></i>
                                <span class="fw-semibold">{{ $document->formatted_download_count }}</span>
                                <small class="text-muted ms-1">kali</small>
                            </span>
                        </dd>
                        
                        <dt class="col-sm-5 text-muted">Dibuat</dt>
                        <dd class="col-sm-7">
                            {{ $document->creator->name ?? '-' }}<br>
                            <small class="text-muted">{{ $document->created_at->format('d/m/Y H:i') }}</small>
                        </dd>
                        
                        <dt class="col-sm-5 text-muted">Terakhir Update</dt>
                        <dd class="col-sm-7 mb-0">
                            {{ $document->updater->name ?? '-' }}<br>
                            <small class="text-muted">{{ $document->updated_at->format('d/m/Y H:i') }}</small>
                        </dd>
                    </dl>
                </div>
            </div>
            
            {{-- Access Management --}}
            @can('documents.manage_access')
            <div class="glass-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Akses Khusus</h5>
                    <a href="{{ route('documents.access', $document) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-person-plus me-1"></i>Kelola
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($document->accessPermissions->take(5) as $access)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span>{{ $access->user->name ?? '-' }}</span>
                            <span class="badge bg-light text-dark">{{ App\Models\DocumentAccess::PERMISSIONS[$access->permission] ?? $access->permission }}</span>
                        </div>
                        @empty
                        <div class="list-group-item text-muted text-center py-3">
                            Tidak ada akses khusus
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endcan
            
            {{-- Related Documents --}}
            @if($relatedDocuments->count() > 0)
            <div class="glass-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-link-45deg me-2"></i>Dokumen Terkait
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($relatedDocuments as $related)
                        <a href="{{ route('documents.show', $related) }}" class="list-group-item list-group-item-action py-3">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div class="flex-grow-1 me-2">
                                    <div class="fw-medium text-truncate mb-1" style="max-width: 200px;" title="{{ $related->title }}">
                                        {{ Str::limit($related->title, 40) }}
                                    </div>
                                    <div class="d-flex align-items-center gap-2 small text-muted">
                                        <span class="badge bg-light text-dark">{{ $related->type->name ?? '-' }}</span>
                                        @if($related->unit)
                                        <span>{{ Str::limit($related->unit->name, 15) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">
                                        {{ $related->document_date?->format('d/m/Y') }}
                                    </small>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('documents.index', ['type' => $document->document_type_id]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-right me-1"></i>Lihat Semua
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Upload Version Modal --}}
<div class="modal fade" id="uploadVersionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('documents.upload-version', $document) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Unggah Versi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="file" accept=".pdf,.doc,.docx" required>
                        <small class="text-muted">Format: PDF, DOC, DOCX. Maksimal 50MB</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan Perubahan</label>
                        <textarea class="form-control" name="change_notes" rows="3" placeholder="Jelaskan perubahan pada versi ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Unggah</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Approve Modal --}}
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('documents.approve', $document) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Setujui Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Anda akan menyetujui dokumen:</p>
                    <p class="fw-medium">{{ $document->title }}</p>
                    <div class="mb-3">
                        <label class="form-label">Catatan (opsional)</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Catatan approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Setujui</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('documents.reject', $document) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Anda akan menolak dokumen:</p>
                    <p class="fw-medium">{{ $document->title }}</p>
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="rejection_reason" rows="3" placeholder="Jelaskan alasan penolakan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Submit for Approval Modal --}}
<div class="modal fade" id="submitApprovalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('documents.submit-approval', $document) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Ajukan untuk Approval</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Dokumen ini akan diajukan untuk approval.</p>
                    <p class="text-muted small">Approver dapat ditambahkan setelah diajukan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Ajukan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
