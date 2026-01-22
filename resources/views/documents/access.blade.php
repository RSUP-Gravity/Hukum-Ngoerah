@extends('layouts.app')

@section('title', 'Akses Dokumen')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Dokumen', 'url' => route('documents.index')],
        ['label' => $document->title, 'url' => route('documents.show', $document)],
        ['label' => 'Manajemen Akses']
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Manajemen Akses Dokumen</h1>
            <p class="text-muted mb-0">{{ $document->document_number }} - {{ Str::limit($document->title, 50) }}</p>
        </div>
        <a href="{{ route('documents.show', $document) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row g-4">
        {{-- Add Access Form --}}
        <div class="col-lg-4">
            <div class="glass-card">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">Tambah Akses</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('documents.access.store', $document) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Tipe Akses</label>
                            <select name="access_type" id="accessType" class="form-select" onchange="toggleAccessTarget()">
                                <option value="user">Pengguna Spesifik</option>
                                <option value="unit">Unit/Bagian</option>
                                <option value="role">Role/Peran</option>
                            </select>
                        </div>
                        
                        {{-- User Selection --}}
                        <div id="userSelect" class="mb-3">
                            <label class="form-label">Pilih Pengguna</label>
                            <select name="user_id" class="form-select">
                                <option value="">-- Pilih Pengguna --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->nip }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Unit Selection --}}
                        <div id="unitSelect" class="mb-3 d-none">
                            <label class="form-label">Pilih Unit</label>
                            <select name="unit_id" class="form-select">
                                <option value="">-- Pilih Unit --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Role Selection --}}
                        <div id="roleSelect" class="mb-3 d-none">
                            <label class="form-label">Pilih Role</label>
                            <select name="role_id" class="form-select">
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->display_name ?? $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Level Akses</label>
                            <select name="access_level" class="form-select">
                                <option value="view">Baca Saja</option>
                                <option value="download">Baca & Download</option>
                                <option value="edit">Baca, Download & Edit</option>
                                <option value="full">Akses Penuh</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Berlaku Sampai</label>
                            <input type="date" name="expires_at" class="form-control" 
                                   min="{{ now()->addDay()->format('Y-m-d') }}">
                            <small class="text-muted">Kosongkan jika tidak ada batas waktu</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-lg me-2"></i>Tambah Akses
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        {{-- Access List --}}
        <div class="col-lg-8">
            <div class="glass-card">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">Daftar Akses</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Akses Untuk</th>
                                    <th>Level</th>
                                    <th>Diberikan Oleh</th>
                                    <th>Berlaku Sampai</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accesses as $access)
                                <tr>
                                    <td>
                                        @if($access->user_id)
                                            <i class="bi bi-person me-1 text-primary"></i>
                                            {{ $access->user->name ?? '-' }}
                                            <span class="badge bg-light text-dark">Pengguna</span>
                                        @elseif($access->unit_id)
                                            <i class="bi bi-building me-1 text-success"></i>
                                            {{ $access->unit->name ?? '-' }}
                                            <span class="badge bg-light text-dark">Unit</span>
                                        @elseif($access->role_id)
                                            <i class="bi bi-shield me-1 text-warning"></i>
                                            {{ $access->role->display_name ?? $access->role->name ?? '-' }}
                                            <span class="badge bg-light text-dark">Role</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $levelClass = match($access->permission) {
                                                'full' => 'bg-success',
                                                'edit' => 'bg-info',
                                                'download' => 'bg-primary',
                                                default => 'bg-secondary'
                                            };
                                            $levelLabel = match($access->permission) {
                                                'full' => 'Akses Penuh',
                                                'edit' => 'Baca, Download & Edit',
                                                'download' => 'Baca & Download',
                                                default => 'Baca Saja'
                                            };
                                        @endphp
                                        <span class="badge {{ $levelClass }}">{{ $levelLabel }}</span>
                                    </td>
                                    <td>
                                        {{ $access->grantedBy->name ?? '-' }}
                                        <div class="small text-muted">{{ $access->created_at->format('d M Y') }}</div>
                                    </td>
                                    <td>
                                        @if($access->valid_until)
                                            @if($access->valid_until->isPast())
                                                <span class="text-danger">
                                                    <i class="bi bi-exclamation-circle me-1"></i>
                                                    Kadaluarsa
                                                </span>
                                            @else
                                                {{ $access->valid_until->format('d M Y') }}
                                            @endif
                                        @else
                                            <span class="text-muted">Tidak terbatas</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('documents.access.destroy', [$document, $access]) }}" method="POST"
                                              onsubmit="return confirm('Yakin ingin mencabut akses ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-lock fs-3 d-block mb-2 opacity-25"></i>
                                            <p class="mb-0">Belum ada akses khusus yang diberikan.</p>
                                            <small>Dokumen ini hanya dapat diakses oleh pemilik dan administrator.</small>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            {{-- Access Info --}}
            <div class="glass-card mt-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Akses</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-muted mb-2">Baca Saja (view)</h6>
                                <p class="mb-0 small">Pengguna hanya dapat melihat informasi dokumen tanpa dapat mengunduh.</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-muted mb-2">Baca & Download</h6>
                                <p class="mb-0 small">Pengguna dapat melihat dan mengunduh file dokumen.</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-muted mb-2">Baca, Download & Edit</h6>
                                <p class="mb-0 small">Pengguna dapat melihat, mengunduh, dan mengedit dokumen.</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-muted mb-2">Akses Penuh</h6>
                                <p class="mb-0 small">Pengguna memiliki semua akses termasuk mengelola akses dokumen.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleAccessTarget() {
    const type = document.getElementById('accessType').value;
    
    document.getElementById('userSelect').classList.add('d-none');
    document.getElementById('unitSelect').classList.add('d-none');
    document.getElementById('roleSelect').classList.add('d-none');
    
    if (type === 'user') {
        document.getElementById('userSelect').classList.remove('d-none');
    } else if (type === 'unit') {
        document.getElementById('unitSelect').classList.remove('d-none');
    } else if (type === 'role') {
        document.getElementById('roleSelect').classList.remove('d-none');
    }
}
</script>
@endpush
