@extends('layouts.app')

@section('title', 'Jabatan')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Master Data'],
        ['label' => 'Jabatan']
    ]" />
@endsection

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Jabatan</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">Kelola data jabatan organisasi</p>
        </div>
        @can('master.create')
            <x-button type="button" @click="$dispatch('open-modal', 'createPositionModal')">
                <i class="bi bi-plus-lg"></i>
                Tambah Jabatan
            </x-button>
        @endcan
    </div>

    {{-- Table --}}
    <x-table>
        <x-slot name="header">
            <th>Kode</th>
            <th>Nama Jabatan</th>
            <th>Level</th>
            <th>Dapat Approve</th>
            <th>Status</th>
            <th class="text-right">Aksi</th>
        </x-slot>

        @forelse($positions as $position)
            <tr>
                <td class="text-xs font-mono text-[var(--text-tertiary)]">{{ $position->code }}</td>
                <td>
                    <div class="text-sm font-semibold text-[var(--text-primary)]">{{ $position->name }}</div>
                    @if($position->description)
                        <div class="text-xs text-[var(--text-tertiary)]">{{ Str::limit($position->description, 50) }}</div>
                    @endif
                </td>
                <td>
                    <x-badge type="info" size="sm">Level {{ $position->level }}</x-badge>
                </td>
                <td>
                    @if($position->can_approve_documents)
                        <x-badge type="success" size="sm"><i class="bi bi-check"></i> Ya</x-badge>
                    @else
                        <x-badge type="default" size="sm">Tidak</x-badge>
                    @endif
                </td>
                <td>
                    @if($position->is_active)
                        <x-badge type="success" size="sm">Aktif</x-badge>
                    @else
                        <x-badge type="expired" size="sm">Nonaktif</x-badge>
                    @endif
                </td>
                <td class="text-right">
                    <div class="flex justify-end gap-2">
                        @can('master.edit')
                            <x-button type="button" size="sm" variant="secondary" onclick='editItem(@json($position))'>
                                <i class="bi bi-pencil"></i>
                            </x-button>
                        @endcan
                        @can('master.delete')
                            <form action="{{ route('master.positions.destroy', $position) }}" method="POST"
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
                        <i class="bi bi-person-badge text-3xl opacity-40"></i>
                        <p class="text-sm">Tidak ada data jabatan.</p>
                    </div>
                </td>
            </tr>
        @endforelse

        @if($positions->hasPages())
            <x-slot name="pagination">
                {{ $positions->withQueryString()->links() }}
            </x-slot>
        @endif
    </x-table>
</div>

{{-- Create Modal --}}
<x-modal name="createPositionModal" maxWidth="xl">
    <x-slot name="header">
        <h3 class="text-lg font-semibold text-[var(--text-primary)]">Tambah Jabatan</h3>
    </x-slot>

    <form action="{{ route('master.positions.store') }}" method="POST" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-input name="code" label="Kode" :required="true" maxlength="20" />
            <x-input name="name" label="Nama" :required="true" />
        </div>
        <x-textarea name="description" label="Deskripsi" rows="2" />
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="space-y-1.5">
                <x-input type="number" name="level" label="Level" value="1" min="1" max="10" />
                <p class="text-xs text-[var(--text-tertiary)]">1 = tertinggi, 10 = terendah</p>
            </div>
            <x-input type="number" name="sort_order" label="Urutan" value="0" min="0" />
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-[var(--text-secondary)]">
            <input class="h-4 w-4 rounded border-[var(--surface-glass-border)] text-primary-500 focus:ring-primary-500" type="checkbox" name="can_approve_documents" value="1">
            Dapat Approve Dokumen
        </label>

        <label class="inline-flex items-center gap-2 text-sm text-[var(--text-secondary)]">
            <input class="h-4 w-4 rounded border-[var(--surface-glass-border)] text-primary-500 focus:ring-primary-500" type="checkbox" name="is_active" value="1" checked>
            Aktif
        </label>

        <div class="flex flex-col-reverse gap-2 border-t border-[var(--surface-glass-border)] pt-4 sm:flex-row sm:justify-end">
            <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'createPositionModal')">Batal</x-button>
            <x-button type="submit">Simpan</x-button>
        </div>
    </form>
</x-modal>

{{-- Edit Modal --}}
<x-modal name="editPositionModal" maxWidth="xl">
    <x-slot name="header">
        <h3 class="text-lg font-semibold text-[var(--text-primary)]">Edit Jabatan</h3>
    </x-slot>

    <form id="editForm" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-input name="code" label="Kode" :required="true" maxlength="20" id="edit_code" />
            <x-input name="name" label="Nama" :required="true" id="edit_name" />
        </div>
        <x-textarea name="description" label="Deskripsi" rows="2" id="edit_description" />
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-input type="number" name="level" label="Level" min="1" max="10" id="edit_level" />
            <x-input type="number" name="sort_order" label="Urutan" min="0" id="edit_sort_order" />
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-[var(--text-secondary)]">
            <input class="h-4 w-4 rounded border-[var(--surface-glass-border)] text-primary-500 focus:ring-primary-500" type="checkbox" name="can_approve_documents" id="edit_can_approve" value="1">
            Dapat Approve Dokumen
        </label>

        <label class="inline-flex items-center gap-2 text-sm text-[var(--text-secondary)]">
            <input class="h-4 w-4 rounded border-[var(--surface-glass-border)] text-primary-500 focus:ring-primary-500" type="checkbox" name="is_active" id="edit_is_active" value="1">
            Aktif
        </label>

        <div class="flex flex-col-reverse gap-2 border-t border-[var(--surface-glass-border)] pt-4 sm:flex-row sm:justify-end">
            <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'editPositionModal')">Batal</x-button>
            <x-button type="submit">Simpan</x-button>
        </div>
    </form>
</x-modal>
@endsection

@push('scripts')
<script>
function editItem(item) {
    document.getElementById('editForm').action = '/master/positions/' + item.id;
    document.getElementById('edit_code').value = item.code;
    document.getElementById('edit_name').value = item.name;
    document.getElementById('edit_description').value = item.description || '';
    document.getElementById('edit_level').value = item.level;
    document.getElementById('edit_sort_order').value = item.sort_order;
    document.getElementById('edit_can_approve').checked = item.can_approve_documents;
    document.getElementById('edit_is_active').checked = item.is_active;
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editPositionModal' }));
}
</script>
@endpush
