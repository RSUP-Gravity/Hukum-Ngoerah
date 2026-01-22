@extends('layouts.app')

@section('title', 'Edit Dokumen')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Dokumen', 'url' => route('documents.index')],
        ['label' => Str::limit($document->title, 30), 'url' => route('documents.show', $document)],
        ['label' => 'Edit']
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            {{-- Page Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Edit Dokumen</h1>
                    <p class="text-muted mb-0">{{ $document->document_number }}</p>
                </div>
                <a href="{{ route('documents.show', $document) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>

            <form action="{{ route('documents.update', $document) }}" method="POST" enctype="multipart/form-data" id="documentForm">
                @csrf
                @method('PUT')
                
                <div class="row">
                    {{-- Main Form --}}
                    <div class="col-lg-8">
                        <div class="glass-card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-file-earmark-text me-2"></i>Informasi Dokumen
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Judul Dokumen <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $document->title) }}" 
                                           placeholder="Masukkan judul dokumen" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="document_number" class="form-label">Nomor Dokumen</label>
                                        <input type="text" class="form-control @error('document_number') is-invalid @enderror" 
                                               id="document_number" name="document_number" value="{{ old('document_number', $document->document_number) }}">
                                        @error('document_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="document_date" class="form-label">Tanggal Dokumen <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('document_date') is-invalid @enderror" 
                                               id="document_date" name="document_date" 
                                               value="{{ old('document_date', $document->document_date?->format('Y-m-d')) }}" required>
                                        @error('document_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="document_type_id" class="form-label">Jenis Dokumen <span class="text-danger">*</span></label>
                                        <select class="form-select @error('document_type_id') is-invalid @enderror" 
                                                id="document_type_id" name="document_type_id" required>
                                            <option value="">Pilih Jenis</option>
                                            @foreach($types as $type)
                                                <option value="{{ $type->id }}" {{ old('document_type_id', $document->document_type_id) == $type->id ? 'selected' : '' }}>
                                                    {{ $type->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('document_type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="document_category_id" class="form-label">Kategori</label>
                                        <select class="form-select @error('document_category_id') is-invalid @enderror" 
                                                id="document_category_id" name="document_category_id">
                                            <option value="">Pilih Kategori</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" 
                                                        {{ old('document_category_id', $document->document_category_id) == $category->id ? 'selected' : '' }}
                                                        data-type="{{ $category->document_type_id }}">
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('document_category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="parties" class="form-label">Pihak-pihak Terkait</label>
                                    <input type="text" class="form-control @error('parties') is-invalid @enderror" 
                                           id="parties" name="parties" value="{{ old('parties', $document->parties) }}"
                                           placeholder="Contoh: PT ABC dengan Rumah Sakit XYZ">
                                    @error('parties')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="4"
                                              placeholder="Deskripsi singkat tentang dokumen ini">{{ old('description', $document->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Catatan Internal</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="2"
                                              placeholder="Catatan internal (tidak ditampilkan ke publik)">{{ old('notes', $document->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        {{-- File Upload --}}
                        <div class="glass-card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-upload me-2"></i>File Dokumen
                                </h5>
                            </div>
                            <div class="card-body">
                                {{-- Current File Info --}}
                                @if($document->currentVersion)
                                <div class="mb-3">
                                    <label class="form-label">File Saat Ini</label>
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="bi bi-file-earmark-pdf fs-2 text-danger me-3"></i>
                                        <div class="flex-grow-1">
                                            <div class="fw-medium">{{ $document->currentVersion->original_filename }}</div>
                                            <small class="text-muted">
                                                Versi {{ $document->current_version }} • 
                                                {{ number_format($document->currentVersion->file_size / 1024, 0) }} KB •
                                                {{ $document->currentVersion->created_at->format('d/m/Y H:i') }}
                                            </small>
                                        </div>
                                        <a href="{{ route('documents.download', $document) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    </div>
                                </div>
                                @endif
                                
                                <div class="mb-3">
                                    <label for="file" class="form-label">Unggah Versi Baru</label>
                                    <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                           id="file" name="file" accept=".pdf,.doc,.docx">
                                    @error('file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Format: PDF, DOC, DOCX. Maksimal 50MB. Kosongkan jika tidak ingin mengganti file.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Sidebar --}}
                    <div class="col-lg-4">
                        {{-- Validity Period --}}
                        <div class="glass-card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-calendar-range me-2"></i>Masa Berlaku
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="effective_date" class="form-label">Tanggal Berlaku</label>
                                    <input type="date" class="form-control @error('effective_date') is-invalid @enderror" 
                                           id="effective_date" name="effective_date" 
                                           value="{{ old('effective_date', $document->effective_date?->format('Y-m-d')) }}">
                                    @error('effective_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="expiry_date" class="form-label">Tanggal Kedaluwarsa</label>
                                    <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                           id="expiry_date" name="expiry_date" 
                                           value="{{ old('expiry_date', $document->expiry_date?->format('Y-m-d')) }}">
                                    @error('expiry_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Kosongkan jika tidak ada batas waktu</small>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Classification --}}
                        <div class="glass-card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-shield-lock me-2"></i>Klasifikasi
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="confidentiality" class="form-label">Tingkat Kerahasiaan <span class="text-danger">*</span></label>
                                    <select class="form-select @error('confidentiality') is-invalid @enderror" 
                                            id="confidentiality" name="confidentiality" required>
                                        <option value="public" {{ old('confidentiality', $document->confidentiality) === 'public' ? 'selected' : '' }}>Publik</option>
                                        <option value="internal" {{ old('confidentiality', $document->confidentiality) === 'internal' ? 'selected' : '' }}>Internal</option>
                                        <option value="confidential" {{ old('confidentiality', $document->confidentiality) === 'confidential' ? 'selected' : '' }}>Rahasia</option>
                                        <option value="secret" {{ old('confidentiality', $document->confidentiality) === 'secret' ? 'selected' : '' }}>Sangat Rahasia</option>
                                    </select>
                                    @error('confidentiality')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="unit_id" class="form-label">Unit Pengelola</label>
                                    <select class="form-select @error('unit_id') is-invalid @enderror" 
                                            id="unit_id" name="unit_id">
                                        <option value="">Pilih Unit</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ old('unit_id', $document->unit_id) == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('unit_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        {{-- Submit Actions --}}
                        <div class="glass-card">
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-2"></i>Simpan Perubahan
                                    </button>
                                    <a href="{{ route('documents.show', $document) }}" class="btn btn-outline-secondary">
                                        Batal
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Unsaved Changes Warning
    const form = document.getElementById('documentForm');
    let formDirty = false;
    let isSubmitting = false;
    
    // Track form changes
    const formInputs = form.querySelectorAll('input, select, textarea');
    formInputs.forEach(input => {
        input.addEventListener('change', () => { formDirty = true; });
        input.addEventListener('input', () => { formDirty = true; });
    });
    
    // Reset dirty flag on form submit
    form.addEventListener('submit', function() {
        isSubmitting = true;
    });
    
    // Warn user before leaving
    window.addEventListener('beforeunload', function(e) {
        if (formDirty && !isSubmitting) {
            e.preventDefault();
            e.returnValue = 'Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman ini?';
            return e.returnValue;
        }
    });
    
    // Intercept navigation links
    document.querySelectorAll('a[href]').forEach(link => {
        link.addEventListener('click', function(e) {
            if (formDirty && !isSubmitting) {
                if (!confirm('Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman ini?')) {
                    e.preventDefault();
                }
            }
        });
    });
    
    // Dynamic category filtering
    const typeSelect = document.getElementById('document_type_id');
    const categorySelect = document.getElementById('document_category_id');
    const categoryOptions = categorySelect.querySelectorAll('option[data-type]');
    const currentCategory = categorySelect.value;
    
    typeSelect.addEventListener('change', function() {
        const selectedType = this.value;
        
        // Reset selection only if type changed
        if (categorySelect.querySelector(`option[value="${currentCategory}"]`)?.dataset.type !== selectedType) {
            categorySelect.value = '';
        }
        
        categoryOptions.forEach(option => {
            if (!selectedType || option.dataset.type === selectedType) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
    });
    
    // Trigger on page load
    typeSelect.dispatchEvent(new Event('change'));
});
</script>
@endpush
