@extends('layouts.app')

@section('title', 'Jenis Dokumen')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Master Data'],
        ['label' => 'Jenis Dokumen']
    ]" />
@endsection

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Jenis Dokumen</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">Kelola klasifikasi jenis dokumen</p>
        </div>
        @can('master.create')
            <x-button type="button" @click="$dispatch('open-modal', 'createTypeModal')">
                <i class="bi bi-plus-lg"></i>
                Tambah Jenis
            </x-button>
        @endcan
    </div>

    {{-- Table --}}
    <x-table>
        <x-slot name="header">
            <th>Kode</th>
            <th>Nama Jenis</th>
            <th>Prefix</th>
            <th>Kategori</th>
            <th>Urutan</th>
            <th>Status</th>
            <th class="text-right">Aksi</th>
        </x-slot>

        @forelse($documentTypes as $type)
            <tr>
                <td class="text-xs font-mono text-[var(--text-tertiary)]">{{ $type->code }}</td>
                <td>
                    <div class="text-sm font-semibold text-[var(--text-primary)]">{{ $type->name }}</div>
                    @if($type->description)
                        <div class="text-xs text-[var(--text-tertiary)]">{{ Str::limit($type->description, 50) }}</div>
                    @endif
                </td>
                <td class="text-xs font-mono text-[var(--text-tertiary)]">{{ $type->prefix }}</td>
                <td class="text-sm text-[var(--text-primary)]">{{ $type->categories_count ?? 0 }} kategori</td>
                <td class="text-sm text-[var(--text-primary)]">{{ $type->sort_order }}</td>
                <td>
                    @if($type->is_active)
                        <x-badge type="success" size="sm">Aktif</x-badge>
                    @else
                        <x-badge type="expired" size="sm">Nonaktif</x-badge>
                    @endif
                </td>
                <td class="text-right">
                    <div class="flex justify-end gap-2">
                        @can('master.edit')
                            <x-button type="button" size="sm" variant="secondary" onclick='editItem(@json($type))'>
                                <i class="bi bi-pencil"></i>
                            </x-button>
                        @endcan
                        @can('master.delete')
                            @if(($type->categories_count ?? 0) === 0)
                                <form action="{{ route('master.document-types.destroy', $type) }}" method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" size="sm" variant="danger">
                                        <i class="bi bi-trash"></i>
                                    </x-button>
                                </form>
                            @endif
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="py-10 text-center">
                    <div class="space-y-3 text-[var(--text-tertiary)]">
                        <i class="bi bi-file-earmark-text text-3xl opacity-40"></i>
                        <p class="text-sm">Tidak ada data jenis dokumen.</p>
                    </div>
                </td>
            </tr>
        @endforelse

        @if($documentTypes->hasPages())
            <x-slot name="pagination">
                {{ $documentTypes->withQueryString()->links() }}
            </x-slot>
        @endif
    </x-table>
</div>

{{-- Create Modal --}}
<x-modal name="createTypeModal" maxWidth="xl">
    <x-slot name="header">
        <h3 class="text-lg font-semibold text-[var(--text-primary)]">Tambah Jenis Dokumen</h3>
    </x-slot>

    <form action="{{ route('master.document-types.store') }}" method="POST" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-input name="code" label="Kode" :required="true" maxlength="20" />
            <x-input name="prefix" label="Prefix Nomor" maxlength="10" />
        </div>
        <x-input name="name" label="Nama" :required="true" />
        <x-textarea name="description" label="Deskripsi" rows="2" />
        <x-input type="number" name="sort_order" label="Urutan" value="0" min="0" />

        <label class="inline-flex items-center gap-2 text-sm text-[var(--text-secondary)]">
            <input class="h-4 w-4 rounded border-[var(--surface-glass-border)] text-primary-500 focus:ring-primary-500" type="checkbox" name="is_active" value="1" checked>
            Aktif
        </label>

        <div class="flex flex-col-reverse gap-2 border-t border-[var(--surface-glass-border)] pt-4 sm:flex-row sm:justify-end">
            <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'createTypeModal')">Batal</x-button>
            <x-button type="submit">Simpan</x-button>
        </div>
    </form>
</x-modal>

{{-- Edit Modal --}}
<x-modal name="editTypeModal" maxWidth="xl">
    <x-slot name="header">
        <h3 class="text-lg font-semibold text-[var(--text-primary)]">Edit Jenis Dokumen</h3>
    </x-slot>

    <form id="editForm" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-input name="code" label="Kode" :required="true" maxlength="20" id="edit_code" />
            <x-input name="prefix" label="Prefix Nomor" maxlength="10" id="edit_prefix" />
        </div>
        <x-input name="name" label="Nama" :required="true" id="edit_name" />
        <x-textarea name="description" label="Deskripsi" rows="2" id="edit_description" />
        <x-input type="number" name="sort_order" label="Urutan" min="0" id="edit_sort_order" />

        <label class="inline-flex items-center gap-2 text-sm text-[var(--text-secondary)]">
            <input class="h-4 w-4 rounded border-[var(--surface-glass-border)] text-primary-500 focus:ring-primary-500" type="checkbox" name="is_active" id="edit_is_active" value="1">
            Aktif
        </label>

        <div class="flex flex-col-reverse gap-2 border-t border-[var(--surface-glass-border)] pt-4 sm:flex-row sm:justify-end">
            <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'editTypeModal')">Batal</x-button>
            <x-button type="submit">Simpan</x-button>
        </div>
    </form>
</x-modal>
@endsection

@push('scripts')
<script>
function editItem(item) {
    document.getElementById('editForm').action = '/master/document-types/' + item.id;
    document.getElementById('edit_code').value = item.code;
    document.getElementById('edit_prefix').value = item.prefix || '';
    document.getElementById('edit_name').value = item.name;
    document.getElementById('edit_description').value = item.description || '';
    document.getElementById('edit_sort_order').value = item.sort_order;
    document.getElementById('edit_is_active').checked = item.is_active;
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editTypeModal' }));
}
</script>
@endpush
