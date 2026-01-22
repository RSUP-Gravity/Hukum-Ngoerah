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
    <x-glass-card :hover="false" class="p-6">
        <form action="{{ route('master.document-categories.index') }}" method="GET" class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-6">
                <label class="text-sm font-medium text-[var(--text-primary)]">Pencarian</label>
                <div class="relative mt-2">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]"></i>
                    <input type="text" class="glass-input pl-10" name="search"
                           value="{{ request('search') }}" placeholder="Cari kategori...">
                </div>
            </div>
            <div class="lg:col-span-3">
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
            <div class="lg:col-span-3 flex flex-wrap items-end gap-2">
                <x-button type="submit" size="sm">
                    <i class="bi bi-funnel"></i>
                    Filter
                </x-button>
                <x-button href="{{ route('master.document-categories.index') }}" size="sm" variant="secondary">
                    <i class="bi bi-x-lg"></i>
                </x-button>
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
                            <x-button type="button" size="sm" variant="secondary" onclick='editItem(@json($category))'>
                                <i class="bi bi-pencil"></i>
                            </x-button>
                        @endcan
                        @can('master.delete')
                            <form action="{{ route('master.document-categories.destroy', $category) }}" method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus?')">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" size="sm" variant="danger">
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
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-[var(--text-primary)]">Jenis Dokumen <span class="text-red-500" aria-hidden="true">*</span></label>
            <select class="glass-input" name="document_type_id" required>
                <option value="">Pilih Jenis</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-input name="code" label="Kode" :required="true" maxlength="20" />
            <x-input name="name" label="Nama" :required="true" />
        </div>
        <x-textarea name="description" label="Deskripsi" rows="2" />
        <x-input type="number" name="sort_order" label="Urutan" value="0" min="0" />

        <label class="inline-flex items-center gap-2 text-sm text-[var(--text-secondary)]">
            <input class="h-4 w-4 rounded border-[var(--surface-glass-border)] text-primary-500 focus:ring-primary-500" type="checkbox" name="is_active" value="1" checked>
            Aktif
        </label>

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
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-[var(--text-primary)]">Jenis Dokumen <span class="text-red-500" aria-hidden="true">*</span></label>
            <select class="glass-input" name="document_type_id" id="edit_type_id" required>
                <option value="">Pilih Jenis</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-input name="code" label="Kode" :required="true" maxlength="20" id="edit_code" />
            <x-input name="name" label="Nama" :required="true" id="edit_name" />
        </div>
        <x-textarea name="description" label="Deskripsi" rows="2" id="edit_description" />
        <x-input type="number" name="sort_order" label="Urutan" min="0" id="edit_sort_order" />

        <label class="inline-flex items-center gap-2 text-sm text-[var(--text-secondary)]">
            <input class="h-4 w-4 rounded border-[var(--surface-glass-border)] text-primary-500 focus:ring-primary-500" type="checkbox" name="is_active" id="edit_is_active" value="1">
            Aktif
        </label>

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
