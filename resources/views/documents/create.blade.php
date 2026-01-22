@extends('layouts.app')

@section('title', 'Tambah Dokumen')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Dokumen', 'url' => route('documents.index')],
        ['label' => 'Tambah']
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            {{-- Page Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Tambah Dokumen</h1>
                    <p class="text-muted mb-0">Buat dokumen hukum baru</p>
                </div>
                <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>

            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" id="documentForm">
                @csrf
                
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
                                           id="title" name="title" value="{{ old('title') }}" 
                                           placeholder="Masukkan judul dokumen" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="document_number" class="form-label">Nomor Dokumen</label>
                                        <input type="text" class="form-control @error('document_number') is-invalid @enderror" 
                                               id="document_number" name="document_number" value="{{ old('document_number') }}"
                                               placeholder="Otomatis jika kosong">
                                        @error('document_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Kosongkan untuk generate otomatis</small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="document_date" class="form-label">Tanggal Dokumen <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('document_date') is-invalid @enderror" 
                                               id="document_date" name="document_date" value="{{ old('document_date', date('Y-m-d')) }}" required>
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
                                                <option value="{{ $type->id }}" {{ old('document_type_id') == $type->id ? 'selected' : '' }}>
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
                                                <option value="{{ $category->id }}" {{ old('document_category_id') == $category->id ? 'selected' : '' }}
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
                                           id="parties" name="parties" value="{{ old('parties') }}"
                                           placeholder="Contoh: PT ABC dengan Rumah Sakit XYZ">
                                    @error('parties')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="4"
                                              placeholder="Deskripsi singkat tentang dokumen ini">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Catatan Internal</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="2"
                                              placeholder="Catatan internal (tidak ditampilkan ke publik)">{{ old('notes') }}</textarea>
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
                                <div class="mb-3">
                                    <label for="file" class="form-label">Unggah File <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                           id="file" name="file" accept=".pdf,.doc,.docx" required>
                                    @error('file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Format: PDF, DOC, DOCX. Maksimal 50MB</small>
                                </div>
                                
                                {{-- File Preview --}}
                                <div id="filePreview" class="d-none">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="bi bi-file-earmark-pdf fs-2 text-danger me-3"></i>
                                        <div>
                                            <div id="fileName" class="fw-medium"></div>
                                            <small id="fileSize" class="text-muted"></small>
                                        </div>
                                    </div>
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
                                           id="effective_date" name="effective_date" value="{{ old('effective_date') }}">
                                    @error('effective_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="expiry_date" class="form-label">Tanggal Kedaluwarsa</label>
                                    <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                           id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
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
                                        <option value="public" {{ old('confidentiality') === 'public' ? 'selected' : '' }}>Publik</option>
                                        <option value="internal" {{ old('confidentiality', 'internal') === 'internal' ? 'selected' : '' }}>Internal</option>
                                        <option value="confidential" {{ old('confidentiality') === 'confidential' ? 'selected' : '' }}>Rahasia</option>
                                        <option value="secret" {{ old('confidentiality') === 'secret' ? 'selected' : '' }}>Sangat Rahasia</option>
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
                                            <option value="{{ $unit->id }}" {{ old('unit_id', auth()->user()->unit_id) == $unit->id ? 'selected' : '' }}>
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
                                        <i class="bi bi-check-lg me-2"></i>Simpan sebagai Draft
                                    </button>
                                    <button type="submit" name="submit_for_review" value="1" class="btn btn-outline-primary">
                                        <i class="bi bi-send me-2"></i>Simpan & Ajukan Review
                                    </button>
                                    <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
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
    // Dynamic category filtering
    const typeSelect = document.getElementById('document_type_id');
    const categorySelect = document.getElementById('document_category_id');
    const categoryOptions = categorySelect.querySelectorAll('option[data-type]');
    
    typeSelect.addEventListener('change', function() {
        const selectedType = this.value;
        
        categorySelect.value = '';
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
    
    // File preview
    const fileInput = document.getElementById('file');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            const file = this.files[0];
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            filePreview.classList.remove('d-none');
        } else {
            filePreview.classList.add('d-none');
        }
    });
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});
</script>
@endpush
