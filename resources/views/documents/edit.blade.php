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
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Edit Dokumen</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $document->document_number }}</p>
        </div>
        <x-button href="{{ route('documents.show', $document) }}" variant="secondary">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </x-button>
    </div>

    <form action="{{ route('documents.update', $document) }}" method="POST" enctype="multipart/form-data" id="documentForm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Main Form --}}
            <div class="space-y-6 lg:col-span-2">
                <x-glass-card :hover="false" class="p-6">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-file-earmark-text text-primary-500"></i>
                        <h2 class="text-lg font-semibold text-[var(--text-primary)]">Informasi Dokumen</h2>
                    </div>

                    <div class="mt-5 space-y-4">
                        <x-input name="title" label="Judul Dokumen" placeholder="Masukkan judul dokumen" :required="true" value="{{ old('title', $document->title) }}" />

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <x-input name="document_number" label="Nomor Dokumen" value="{{ old('document_number', $document->document_number) }}" />

                            <x-input type="date" name="document_date" label="Tanggal Dokumen" :required="true" value="{{ old('document_date', $document->document_date?->format('Y-m-d')) }}" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-1.5">
                                <label for="document_type_id" class="block text-sm font-medium text-[var(--text-primary)]">Jenis Dokumen <span class="text-red-500" aria-hidden="true">*</span></label>
                                <select id="document_type_id" name="document_type_id" class="glass-input" required>
                                    <option value="">Pilih Jenis</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" {{ old('document_type_id', $document->document_type_id) == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('document_type_id')
                                    <p class="text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label for="document_category_id" class="block text-sm font-medium text-[var(--text-primary)]">Kategori</label>
                                <select id="document_category_id" name="document_category_id" class="glass-input">
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
                                    <p class="text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <x-input name="parties" label="Pihak-pihak Terkait" placeholder="Contoh: PT ABC dengan Rumah Sakit XYZ" value="{{ old('parties', $document->parties) }}" />

                        <x-textarea name="description" label="Deskripsi" rows="4" placeholder="Deskripsi singkat tentang dokumen ini" value="{{ old('description', $document->description) }}" />

                        <x-textarea name="notes" label="Catatan Internal" rows="2" placeholder="Catatan internal (tidak ditampilkan ke publik)" value="{{ old('notes', $document->notes) }}" />
                    </div>
                </x-glass-card>

                {{-- File Upload --}}
                <x-glass-card :hover="false" class="p-6">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-upload text-primary-500"></i>
                        <h2 class="text-lg font-semibold text-[var(--text-primary)]">File Dokumen</h2>
                    </div>

                    <div class="mt-5 space-y-3">
                        @if($document->currentVersion)
                            <div class="space-y-1.5">
                                <label class="block text-sm font-medium text-[var(--text-primary)]">File Saat Ini</label>
                                <div class="flex items-center gap-3 rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-3">
                                    <i class="bi bi-file-earmark-pdf text-2xl text-red-500"></i>
                                    <div class="flex-1">
                                        <div class="text-sm font-semibold text-[var(--text-primary)]">{{ $document->currentVersion->original_filename }}</div>
                                        <div class="text-xs text-[var(--text-tertiary)]">
                                            Versi {{ $document->current_version }} •
                                            {{ number_format($document->currentVersion->file_size / 1024, 0) }} KB •
                                            {{ $document->currentVersion->created_at->format('d/m/Y H:i') }}
                                        </div>
                                    </div>
                                    <x-button href="{{ route('documents.download', $document) }}" size="sm" variant="secondary">
                                        <i class="bi bi-download"></i>
                                    </x-button>
                                </div>
                            </div>
                        @endif

                        <div class="space-y-1.5">
                            <label for="file" class="block text-sm font-medium text-[var(--text-primary)]">Unggah Versi Baru</label>
                            <input type="file" class="glass-input file:mr-4 file:rounded-lg file:border-0 file:bg-[var(--surface-glass)] file:px-3 file:py-2 file:text-sm file:text-[var(--text-primary)]"
                                   id="file" name="file" accept=".pdf,.doc,.docx">
                            @error('file')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-[var(--text-tertiary)]">Format: PDF, DOC, DOCX. Maksimal 50MB. Kosongkan jika tidak ingin mengganti file.</p>
                        </div>
                    </div>
                </x-glass-card>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                <x-glass-card :hover="false" class="p-6">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-calendar-range text-primary-500"></i>
                        <h2 class="text-lg font-semibold text-[var(--text-primary)]">Masa Berlaku</h2>
                    </div>

                    <div class="mt-5 space-y-4">
                        <x-input type="date" name="effective_date" label="Tanggal Berlaku" value="{{ old('effective_date', $document->effective_date?->format('Y-m-d')) }}" />
                        <x-input type="date" name="expiry_date" label="Tanggal Kedaluwarsa" hint="Kosongkan jika tidak ada batas waktu" value="{{ old('expiry_date', $document->expiry_date?->format('Y-m-d')) }}" />
                    </div>
                </x-glass-card>

                <x-glass-card :hover="false" class="p-6">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-shield-lock text-primary-500"></i>
                        <h2 class="text-lg font-semibold text-[var(--text-primary)]">Klasifikasi</h2>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div class="space-y-1.5">
                            <label for="confidentiality" class="block text-sm font-medium text-[var(--text-primary)]">Tingkat Kerahasiaan <span class="text-red-500" aria-hidden="true">*</span></label>
                            <select id="confidentiality" name="confidentiality" class="glass-input" required>
                                <option value="public" {{ old('confidentiality', $document->confidentiality) === 'public' ? 'selected' : '' }}>Publik</option>
                                <option value="internal" {{ old('confidentiality', $document->confidentiality) === 'internal' ? 'selected' : '' }}>Internal</option>
                                <option value="confidential" {{ old('confidentiality', $document->confidentiality) === 'confidential' ? 'selected' : '' }}>Rahasia</option>
                                <option value="secret" {{ old('confidentiality', $document->confidentiality) === 'secret' ? 'selected' : '' }}>Sangat Rahasia</option>
                            </select>
                            @error('confidentiality')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="unit_id" class="block text-sm font-medium text-[var(--text-primary)]">Unit Pengelola</label>
                            <select id="unit_id" name="unit_id" class="glass-input">
                                <option value="">Pilih Unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_id', $document->unit_id) == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('unit_id')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </x-glass-card>

                <x-glass-card :hover="false" class="p-6">
                    <div class="space-y-2">
                        <x-button type="submit" class="w-full">
                            <i class="bi bi-check-lg"></i>
                            Simpan Perubahan
                        </x-button>
                        <x-button href="{{ route('documents.show', $document) }}" variant="ghost" class="w-full">
                            Batal
                        </x-button>
                    </div>
                </x-glass-card>
            </div>
        </div>
    </form>

    <x-modal name="unsaved-changes" maxWidth="sm">
        <x-slot name="header">
            <div class="flex items-center gap-2 text-sm font-semibold text-[var(--text-primary)]">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-[var(--surface-glass)] text-[var(--text-secondary)]">
                    <i class="bi bi-globe2"></i>
                </span>
                <span>{{ request()->getHttpHost() }}</span>
            </div>
        </x-slot>

        <p class="text-sm text-[var(--text-secondary)] leading-relaxed">
            Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman ini?
        </p>

        <x-slot name="footer">
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-button type="button" variant="secondary" @click="$dispatch('close-modal', 'unsaved-changes')">
                    Cancel
                </x-button>
                <x-button type="button" @click="$dispatch('unsaved-confirm')">
                    OK
                </x-button>
            </div>
        </x-slot>
    </x-modal>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Unsaved Changes Warning
    const form = document.getElementById('documentForm');
    let formDirty = false;
    let isSubmitting = false;
    let isNavigating = false;
    let pendingHref = null;
    const warningMessage = 'Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman ini?';
    
    // Track form changes
    const formInputs = form.querySelectorAll('input, select, textarea');
    formInputs.forEach(input => {
        input.addEventListener('change', () => { formDirty = true; });
        input.addEventListener('input', () => { formDirty = true; });
    });
    
    // Reset dirty flag on form submit
    form.addEventListener('submit', function() {
        isSubmitting = true;
        isNavigating = true;
    });
    
    // Warn user before leaving
    window.addEventListener('beforeunload', function(e) {
        if (formDirty && !isSubmitting && !isNavigating) {
            e.preventDefault();
            e.returnValue = warningMessage;
            return e.returnValue;
        }
    });
    
    // Intercept navigation links
    document.querySelectorAll('a[href]').forEach(link => {
        link.addEventListener('click', function(e) {
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:')) {
                return;
            }
            if (link.target === '_blank' || link.hasAttribute('download')) {
                return;
            }
            if (formDirty && !isSubmitting) {
                e.preventDefault();
                pendingHref = link.href;
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'unsaved-changes' }));
            }
        });
    });

    document.addEventListener('unsaved-confirm', function() {
        if (!pendingHref) {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'unsaved-changes' }));
            return;
        }
        isNavigating = true;
        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'unsaved-changes' }));
        window.location.href = pendingHref;
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
