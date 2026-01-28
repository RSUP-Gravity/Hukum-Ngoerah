@extends('layouts.app')

@section('title', 'Kategori Dokumen')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Master Data'],
        ['label' => 'Kategori Dokumen']
    ]" />
@endsection

@section('content')
@php
    $hasFilters = request()->filled('search') || request()->filled('document_type_id') || request()->filled('active');
    $resultFrom = $categories->firstItem() ?? 0;
    $resultTo = $categories->lastItem() ?? 0;
    $selectedType = $documentTypes->firstWhere('id', (int) request('document_type_id'));
@endphp
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Kategori Dokumen</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">Kelola sub-klasifikasi kategori dokumen</p>
        </div>
        @can('master.create')
            <x-button type="button" @click="$dispatch('open-modal', 'createCategoryModal')">
                <i class="bi bi-plus-lg"></i>
                Tambah Kategori
            </x-button>
        @endcan
    </div>

    {{-- Filters --}}
    <x-glass-card :hover="false" class="p-6 overflow-visible relative z-20">
        <form action="{{ route('master.document-categories.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-4 space-y-1.5">
                    <label class="text-sm font-medium text-[var(--text-primary)]">Pencarian</label>
                    <div class="relative">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]"></i>
                        <input type="text" class="glass-input pl-10" name="search"
                               value="{{ request('search') }}" placeholder="Cari nama atau kode kategori...">
                    </div>
                </div>
                <div class="lg:col-span-4">
                    <x-select
                        name="document_type_id"
                        label="Jenis Dokumen"
                        placeholder="Semua Jenis"
                        :options="$documentTypes->pluck('name', 'id')"
                        :value="request('document_type_id')"
                    />
                </div>
                <div class="lg:col-span-2">
                    <x-select
                        name="active"
                        label="Status"
                        placeholder="Semua Status"
                        :options="['1' => 'Aktif', '0' => 'Nonaktif']"
                        :value="request('active')"
                    />
                </div>
                <div class="lg:col-span-2 flex flex-wrap items-end gap-2 lg:justify-end">
                    <x-button type="submit" size="sm">
                        <i class="bi bi-funnel"></i>
                        Terapkan
                    </x-button>
                    <x-button href="{{ route('master.document-categories.index') }}" size="sm" variant="secondary" class="{{ $hasFilters ? '' : 'pointer-events-none opacity-60' }}">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Reset
                    </x-button>
                </div>
            </div>

            @if($hasFilters)
                <div class="flex flex-wrap items-center gap-2 border-t border-[var(--surface-glass-border)] pt-4">
                    <span class="text-xs font-semibold uppercase tracking-wide text-[var(--text-tertiary)]">Filter aktif</span>
                    @if(request()->filled('search'))
                        <a href="{{ route('master.document-categories.index', request()->except('search', 'page')) }}"
                           class="badge-default inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs transition hover:bg-[var(--surface-glass-elevated)]">
                            <i class="bi bi-search text-[var(--text-tertiary)]"></i>
                            <span>{{ Str::limit(request('search'), 24) }}</span>
                            <i class="bi bi-x-lg text-[var(--text-tertiary)]"></i>
                        </a>
                    @endif
                    @if(request()->filled('document_type_id'))
                        <a href="{{ route('master.document-categories.index', request()->except('document_type_id', 'page')) }}"
                           class="badge-default inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs transition hover:bg-[var(--surface-glass-elevated)]">
                            <i class="bi bi-folder text-[var(--text-tertiary)]"></i>
                            <span>Jenis: {{ $selectedType?->name ?? 'Tidak dikenal' }}</span>
                            <i class="bi bi-x-lg text-[var(--text-tertiary)]"></i>
                        </a>
                    @endif
                    @if(request()->filled('active'))
                        <a href="{{ route('master.document-categories.index', request()->except('active', 'page')) }}"
                           class="badge-default inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs transition hover:bg-[var(--surface-glass-elevated)]">
                            <i class="bi bi-toggle-on text-[var(--text-tertiary)]"></i>
                            <span>Status: {{ request('active') === '1' ? 'Aktif' : 'Nonaktif' }}</span>
                            <i class="bi bi-x-lg text-[var(--text-tertiary)]"></i>
                        </a>
                    @endif
                    <a href="{{ route('master.document-categories.index') }}" class="text-xs text-[var(--text-tertiary)] hover:text-[var(--text-primary)]">
                        Reset semua
                    </a>
                </div>
            @endif

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-[var(--surface-glass-border)] pt-4 text-xs text-[var(--text-tertiary)]">
                <span>Menampilkan {{ $resultFrom }}-{{ $resultTo }} dari {{ $categories->total() }} data</span>
                <span>Urutan: sort order, nama</span>
            </div>
        </form>
    </x-glass-card>

    {{-- Table --}}
    <x-table>
        <x-slot name="header">
            <th>Kode</th>
            <th>Nama Kategori</th>
            <th>Jenis Dokumen</th>
            <th>Urutan</th>
            <th>Status</th>
            <th class="text-right">Aksi</th>
        </x-slot>

        @forelse($categories as $category)
            <tr>
                <td class="text-xs font-mono text-[var(--text-tertiary)]">{{ $category->code }}</td>
                <td>
                    <div class="text-sm font-semibold text-[var(--text-primary)]">{{ $category->name }}</div>
                    @if($category->description)
                        <div class="text-xs text-[var(--text-tertiary)]">{{ Str::limit($category->description, 50) }}</div>
                    @endif
                    <div class="text-xs text-[var(--text-tertiary)]">{{ $category->documents_count ?? 0 }} dokumen</div>
                </td>
                <td>
                    <x-badge type="default" size="sm">{{ $category->documentType->name ?? '-' }}</x-badge>
                </td>
                <td class="text-sm text-[var(--text-primary)]">{{ $category->sort_order }}</td>
                <td>
                    @if($category->is_active)
                        <x-badge type="success" size="sm">Aktif</x-badge>
                    @else
                        <x-badge type="expired" size="sm">Nonaktif</x-badge>
                    @endif
                </td>
                <td class="text-right">
                    <div class="flex justify-end gap-2">
                        @can('master.edit')
                            <x-button type="button" size="sm" variant="secondary" onclick="editItem(@js($category))" aria-label="Edit kategori" title="Edit kategori">
                                <i class="bi bi-pencil"></i>
                            </x-button>
                        @endcan
                        @can('master.delete')
                            <form action="{{ route('master.document-categories.destroy', $category) }}" method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus?')">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" size="sm" variant="danger" aria-label="Hapus kategori" title="Hapus kategori">
                                    <i class="bi bi-trash"></i>
                                </x-button>
                            </form>
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="py-10 text-center">
                    <div class="space-y-3 text-[var(--text-tertiary)]">
                        <i class="bi bi-folder text-3xl opacity-40"></i>
                        <p class="text-sm">Tidak ada data kategori.</p>
                        @can('master.create')
                            <x-button type="button" size="sm" @click="$dispatch('open-modal', 'createCategoryModal')">
                                Tambah Kategori
                            </x-button>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforelse

        @if($categories->hasPages())
            <x-slot name="pagination">
                {{ $categories->withQueryString()->links() }}
            </x-slot>
        @endif
    </x-table>
</div>

{{-- Create Modal --}}
<x-modal name="createCategoryModal" maxWidth="xl">
    <x-slot name="header">
        <h3 class="text-lg font-semibold text-[var(--text-primary)]">Tambah Kategori</h3>
    </x-slot>

    <form action="{{ route('master.document-categories.store') }}" method="POST" class="space-y-4">
        @csrf
        <x-select
            name="document_type_id"
            label="Jenis Dokumen"
            placeholder="Pilih Jenis"
            :options="$documentTypes->pluck('name', 'id')"
            :required="true"
        />
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-input name="code" label="Kode" :required="true" maxlength="20" />
            <x-input name="name" label="Nama" :required="true" />
        </div>
        <x-textarea name="description" label="Deskripsi" rows="2" />
        <x-input type="number" name="sort_order" label="Urutan" min="0" placeholder="Otomatis" hint="Kosongkan untuk urutan otomatis." />

        <div class="flex flex-col gap-3">
            <label class="inline-flex items-center gap-2 text-sm text-[var(--text-secondary)]">
                <input class="h-4 w-4 rounded border-[var(--surface-glass-border)] text-primary-500 focus:ring-primary-500" type="checkbox" name="is_active" value="1" checked>
                Aktif
            </label>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-[var(--surface-glass-border)] pt-4 sm:flex-row sm:justify-end">
            <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'createCategoryModal')">Batal</x-button>
            <x-button type="submit">Simpan</x-button>
        </div>
    </form>
</x-modal>

{{-- Edit Modal --}}
<x-modal name="editCategoryModal" maxWidth="xl">
    <x-slot name="header">
        <h3 class="text-lg font-semibold text-[var(--text-primary)]">Edit Kategori</h3>
    </x-slot>

    <form id="editForm" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        <x-select
            name="document_type_id"
            id="edit_type_id"
            label="Jenis Dokumen"
            placeholder="Pilih Jenis"
            :options="$documentTypes->pluck('name', 'id')"
            :required="true"
        />
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-input name="code" label="Kode" :required="true" maxlength="20" id="edit_code" />
            <x-input name="name" label="Nama" :required="true" id="edit_name" />
        </div>
        <x-textarea name="description" label="Deskripsi" rows="2" id="edit_description" />
        <x-input type="number" name="sort_order" label="Urutan" min="0" id="edit_sort_order" placeholder="Otomatis" hint="Kosongkan untuk urutan otomatis." />

        <div class="flex flex-col gap-3">
            <label class="inline-flex items-center gap-2 text-sm text-[var(--text-secondary)]">
                <input class="h-4 w-4 rounded border-[var(--surface-glass-border)] text-primary-500 focus:ring-primary-500" type="checkbox" name="is_active" id="edit_is_active" value="1">
                Aktif
            </label>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-[var(--surface-glass-border)] pt-4 sm:flex-row sm:justify-end">
            <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'editCategoryModal')">Batal</x-button>
            <x-button type="submit">Simpan</x-button>
        </div>
    </form>
</x-modal>
@endsection

@push('scripts')
<script>
function editItem(item) {
    document.getElementById('editForm').action = '/master/document-categories/' + item.id;
    document.getElementById('edit_type_id').value = item.document_type_id;
    document.getElementById('edit_code').value = item.code;
    document.getElementById('edit_name').value = item.name;
    document.getElementById('edit_description').value = item.description || '';
    document.getElementById('edit_sort_order').value = item.sort_order;
    document.getElementById('edit_is_active').checked = item.is_active;
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editCategoryModal' }));
}
</script>
@endpush
