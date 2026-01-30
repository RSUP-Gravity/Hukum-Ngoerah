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
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Tambah Dokumen</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">Buat dokumen hukum baru</p>
        </div>
        <x-button href="{{ route('documents.index') }}" variant="secondary">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </x-button>
    </div>

    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" id="documentForm"
        x-data="{
            search: '',
            candidates: @js($approverCandidates),
            selected: [],
            init() {
                const selectedIds = (@js(old('approvers', [])) || []).map((id) => Number(id));
                this.selected = this.candidates.filter((candidate) => selectedIds.includes(candidate.id));
            },
            get filteredCandidates() {
                const keyword = this.search.trim().toLowerCase();
                return this.candidates.filter((candidate) => {
                    const meta = this.metaLine(candidate).toLowerCase();
                    const haystack = `${candidate.name} ${meta}`.trim().toLowerCase();
                    return !keyword || haystack.includes(keyword);
                });
            },
            metaLine(candidate) {
                const parts = [candidate.role, candidate.position, candidate.unit].filter((part) => part && part !== '-');
                return parts.length ? parts.join(' - ') : '-';
            },
            isSelected(id) {
                return this.selected.some((candidate) => candidate.id === id);
            },
            toggle(candidate) {
                if (this.isSelected(candidate.id)) {
                    this.remove(candidate.id);
                    return;
                }
                this.selected.push(candidate);
            },
            remove(id) {
                this.selected = this.selected.filter((candidate) => candidate.id !== id);
            },
            moveUp(index) {
                if (index <= 0) {
                    return;
                }
                const temp = this.selected[index - 1];
                this.selected.splice(index - 1, 1, this.selected[index]);
                this.selected.splice(index, 1, temp);
            },
            moveDown(index) {
                if (index >= this.selected.length - 1) {
                    return;
                }
                const temp = this.selected[index + 1];
                this.selected.splice(index + 1, 1, this.selected[index]);
                this.selected.splice(index, 1, temp);
            },
        }">
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Main Form --}}
            <div class="space-y-6 lg:col-span-2">
                <x-glass-card :hover="false" class="p-6">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-file-earmark-text text-primary-500"></i>
                        <h2 class="text-lg font-semibold text-[var(--text-primary)]">Informasi Dokumen</h2>
                    </div>

                    <div class="mt-5 space-y-4">
                        <x-input name="title" label="Judul Dokumen" placeholder="Masukkan judul dokumen" :required="true" />

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <x-input name="document_number" label="Nomor Dokumen" placeholder="Otomatis jika kosong" hint="Kosongkan untuk generate otomatis" />

                            <x-input type="date" name="document_date" label="Tanggal Dokumen" :required="true" value="{{ old('document_date', date('Y-m-d')) }}" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-1.5">
                                <label for="document_type_id" class="block text-sm font-medium text-[var(--text-primary)]">Jenis Dokumen <span class="text-red-500" aria-hidden="true">*</span></label>
                                <select id="document_type_id" name="document_type_id" class="glass-input" required>
                                    <option value="">Pilih Jenis</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" {{ old('document_type_id') == $type->id ? 'selected' : '' }}>
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
                                        <option value="{{ $category->id }}" {{ old('document_category_id') == $category->id ? 'selected' : '' }}
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

                        <x-input name="parties" label="Pihak-pihak Terkait" placeholder="Contoh: PT ABC dengan Rumah Sakit XYZ" />

                        <x-textarea name="description" label="Deskripsi" rows="4" placeholder="Deskripsi singkat tentang dokumen ini" />

                        <x-textarea name="notes" label="Catatan Internal" rows="2" placeholder="Catatan internal (tidak ditampilkan ke publik)" />
                    </div>
                </x-glass-card>

                {{-- File Upload --}}
                <x-glass-card :hover="false" class="p-6">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-upload text-primary-500"></i>
                        <h2 class="text-lg font-semibold text-[var(--text-primary)]">File Dokumen</h2>
                    </div>

                    <div class="mt-5 space-y-3">
                        <div class="space-y-1.5">
                            <label for="file" class="block text-sm font-medium text-[var(--text-primary)]">Unggah File <span class="text-red-500" aria-hidden="true">*</span></label>
                            <input type="file" class="glass-input file:mr-4 file:rounded-lg file:border-0 file:bg-[var(--surface-glass)] file:px-3 file:py-2 file:text-sm file:text-[var(--text-primary)]"
                                   id="file" name="file" accept=".pdf,.doc,.docx" required>
                            @error('file')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-[var(--text-tertiary)]">Format: PDF, DOC, DOCX. Maksimal 50MB</p>
                        </div>

                        {{-- File Preview --}}
                        <div id="filePreview" class="hidden">
                            <div class="flex items-center gap-3 rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-3">
                                <i class="bi bi-file-earmark-pdf text-2xl text-red-500"></i>
                                <div>
                                    <div id="fileName" class="text-sm font-semibold text-[var(--text-primary)]"></div>
                                    <div id="fileSize" class="text-xs text-[var(--text-tertiary)]"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-glass-card>

                @if($isStaff)
                    <x-glass-card :hover="false" class="p-6">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-person-check text-primary-500"></i>
                            <h2 class="text-lg font-semibold text-[var(--text-primary)]">Pilih Approver</h2>
                        </div>
                        <div class="mt-4 space-y-2 text-sm text-[var(--text-secondary)]">
                            <p>Dokumen akan langsung diajukan untuk approval setelah disimpan.</p>
                            <p class="text-xs text-[var(--text-tertiary)]">Urutan approval mengikuti daftar di sisi kanan.</p>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-5">
                            <div class="lg:col-span-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-medium text-[var(--text-primary)]">Calon Approver</label>
                                    <span class="text-xs text-[var(--text-tertiary)]" x-text="`${filteredCandidates.length} tersedia`"></span>
                                </div>
                                <div class="relative mt-2">
                                    <input
                                        type="text"
                                        x-model="search"
                                        class="glass-input pr-9"
                                        placeholder="Cari nama, jabatan, atau unit"
                                    >
                                    <i class="bi bi-search pointer-events-none absolute right-3 top-3 text-sm text-[var(--text-tertiary)]"></i>
                                </div>
                                <div class="mt-3 max-h-64 space-y-2 overflow-y-auto pr-1 custom-scrollbar">
                                    <template x-for="candidate in filteredCandidates" :key="candidate.id">
                                        <button
                                            type="button"
                                            class="w-full rounded-xl border px-3 py-2 text-left transition-all"
                                            @click="toggle(candidate)"
                                            :class="isSelected(candidate.id)
                                                ? 'border-primary-400 bg-[var(--surface-elevated)]'
                                                : 'border-[var(--surface-glass-border)] bg-[var(--surface-glass)] hover:border-[var(--surface-glass-border-hover)]'"
                                        >
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <div class="text-sm font-semibold text-[var(--text-primary)]" x-text="candidate.name"></div>
                                                    <div class="text-xs text-[var(--text-tertiary)]" x-text="metaLine(candidate)"></div>
                                                </div>
                                                <div class="text-xs font-medium">
                                                    <span x-show="isSelected(candidate.id)" class="inline-flex items-center gap-1 text-primary-500">
                                                        <i class="bi bi-check-circle"></i>
                                                        Terpilih
                                                    </span>
                                                    <span x-show="!isSelected(candidate.id)" class="inline-flex items-center gap-1 text-[var(--text-tertiary)]">
                                                        <i class="bi bi-plus-circle"></i>
                                                        Tambah
                                                    </span>
                                                </div>
                                            </div>
                                        </button>
                                    </template>
                                    <div
                                        x-show="filteredCandidates.length === 0"
                                        class="rounded-xl border border-dashed border-[var(--surface-glass-border)] p-4 text-center text-xs text-[var(--text-tertiary)]"
                                    >
                                        Tidak ada approver yang cocok.
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-[var(--text-tertiary)]">Hanya pengguna dengan izin approve yang tampil di sini.</p>
                            </div>

                            <div class="lg:col-span-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-medium text-[var(--text-primary)]">Rantai Approval</label>
                                    <span class="text-xs text-[var(--text-tertiary)]" x-text="`${selected.length} dipilih`"></span>
                                </div>
                                <div class="mt-2 space-y-2">
                                    <template x-for="(candidate, index) in selected" :key="candidate.id">
                                        <div class="flex items-center justify-between gap-3 rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] px-3 py-2">
                                            <div class="min-w-0">
                                                <div class="text-sm font-semibold text-[var(--text-primary)]" x-text="`${index + 1}. ${candidate.name}`"></div>
                                                <div class="truncate text-xs text-[var(--text-tertiary)]" x-text="metaLine(candidate)"></div>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <button type="button" class="btn-ghost px-2 py-2" @click="moveUp(index)" :disabled="index === 0" aria-label="Naikkan urutan">
                                                    <i class="bi bi-chevron-up text-base"></i>
                                                </button>
                                                <button type="button" class="btn-ghost px-2 py-2" @click="moveDown(index)" :disabled="index === selected.length - 1" aria-label="Turunkan urutan">
                                                    <i class="bi bi-chevron-down text-base"></i>
                                                </button>
                                                <button type="button" class="btn-ghost px-2 py-2 text-red-500 hover:text-red-600" @click="remove(candidate.id)" aria-label="Hapus approver">
                                                    <i class="bi bi-x-circle text-base"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    <div
                                        x-show="selected.length === 0"
                                        class="rounded-xl border border-dashed border-[var(--surface-glass-border)] p-4 text-center text-xs text-[var(--text-tertiary)]"
                                    >
                                        Pilih minimal 1 approver.
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-[var(--text-tertiary)]">Gunakan tombol panah untuk mengubah urutan.</p>
                            </div>
                        </div>

                        <template x-for="candidate in selected" :key="'input-' + candidate.id">
                            <input type="hidden" name="approvers[]" :value="candidate.id">
                        </template>
                        @error('approvers')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        @error('approvers.*')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </x-glass-card>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                <x-glass-card :hover="false" class="p-6">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-calendar-range text-primary-500"></i>
                        <h2 class="text-lg font-semibold text-[var(--text-primary)]">Masa Berlaku</h2>
                    </div>

                    <div class="mt-5 space-y-4">
                        <x-input type="date" name="effective_date" label="Tanggal Berlaku" />
                        <x-input type="date" name="expiry_date" label="Tanggal Kedaluwarsa" hint="Kosongkan jika tidak ada batas waktu" />
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
                                <option value="public" {{ old('confidentiality') === 'public' ? 'selected' : '' }}>Publik</option>
                                <option value="internal" {{ old('confidentiality', 'internal') === 'internal' ? 'selected' : '' }}>Internal</option>
                                <option value="confidential" {{ old('confidentiality') === 'confidential' ? 'selected' : '' }}>Rahasia</option>
                                <option value="secret" {{ old('confidentiality') === 'secret' ? 'selected' : '' }}>Sangat Rahasia</option>
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
                                    <option value="{{ $unit->id }}" {{ old('unit_id', auth()->user()->unit_id) == $unit->id ? 'selected' : '' }}>
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
                            Simpan sebagai Draft
                        </x-button>
                        @if($isStaff)
                            <x-button type="submit" name="submit_for_approval" value="1" variant="secondary" class="w-full" x-bind:disabled="selected.length === 0">
                                <i class="bi bi-send"></i>
                                Simpan & Ajukan Approval
                            </x-button>
                        @else
                            <x-button type="submit" name="submit_for_review" value="1" variant="secondary" class="w-full">
                                <i class="bi bi-send"></i>
                                Simpan & Ajukan Review
                            </x-button>
                        @endif
                        <x-button href="{{ route('documents.index') }}" variant="ghost" class="w-full">
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
            filePreview.classList.remove('hidden');
            formDirty = true; // Mark dirty when file selected
        } else {
            filePreview.classList.add('hidden');
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
