@extends('layouts.app')

@section('title', 'Unit')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Master Data'],
        ['label' => 'Unit']
    ]" />
@endsection

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Unit</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">Kelola data unit organisasi</p>
        </div>
        @can('master.create')
            <x-button type="button" @click="$dispatch('open-modal', 'createUnitModal')">
                <i class="bi bi-plus-lg"></i>
                Tambah Unit
            </x-button>
        @endcan
    </div>

    {{-- Filters --}}
    <x-glass-card :hover="false" class="p-6">
        <form action="{{ route('master.units.index') }}" method="GET" class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <label class="text-sm font-medium text-[var(--text-primary)]">Pencarian</label>
                <div class="relative mt-2">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]"></i>
                    <input type="text" class="glass-input pl-10" name="search"
                           value="{{ request('search') }}" placeholder="Cari unit...">
                </div>
            </div>
            <div class="lg:col-span-3">
                <label class="text-sm font-medium text-[var(--text-primary)]">Direktorat</label>
                <select name="directorate_id" class="glass-input mt-2">
                    <option value="">Semua Direktorat</option>
                    @foreach($directorates as $directorate)
                        <option value="{{ $directorate->id }}" {{ request('directorate_id') == $directorate->id ? 'selected' : '' }}>
                            {{ $directorate->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2">
                <label class="text-sm font-medium text-[var(--text-primary)]">Status</label>
                <select name="active" class="glass-input mt-2">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="lg:col-span-3 flex flex-wrap items-end gap-2">
                <x-button type="submit" size="sm">
                    <i class="bi bi-funnel"></i>
                    Filter
                </x-button>
                <x-button href="{{ route('master.units.index') }}" size="sm" variant="secondary">
                    <i class="bi bi-x-lg"></i>
                </x-button>
            </div>
        </form>
    </x-glass-card>

    {{-- Table --}}
    <x-table>
        <x-slot name="header">
            <th>Kode</th>
            <th>Nama Unit</th>
            <th>Direktorat</th>
            <th>Urutan</th>
            <th>Status</th>
            <th class="text-right">Aksi</th>
        </x-slot>

        @forelse($units as $unit)
            <tr>
                <td class="text-xs font-mono text-[var(--text-tertiary)]">{{ $unit->code }}</td>
                <td>
                    <div class="text-sm font-semibold text-[var(--text-primary)]">{{ $unit->name }}</div>
                    @if($unit->description)
                        <div class="text-xs text-[var(--text-tertiary)]">{{ Str::limit($unit->description, 50) }}</div>
                    @endif
                </td>
                <td class="text-sm text-[var(--text-primary)]">{{ $unit->directorate->name ?? '-' }}</td>
                <td class="text-sm text-[var(--text-primary)]">{{ $unit->sort_order }}</td>
                <td>
                    @if($unit->is_active)
                        <x-badge type="success" size="sm">Aktif</x-badge>
                    @else
                        <x-badge type="expired" size="sm">Nonaktif</x-badge>
                    @endif
                </td>
                <td class="text-right">
                    <div class="flex justify-end gap-2">
                        @can('master.edit')
                            <x-button type="button" size="sm" variant="secondary" onclick='editItem(@json($unit))'>
                                <i class="bi bi-pencil"></i>
                            </x-button>
                        @endcan
                        @can('master.delete')
                            <form action="{{ route('master.units.destroy', $unit) }}" method="POST"
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
                        <i class="bi bi-diagram-3 text-3xl opacity-40"></i>
                        <p class="text-sm">Tidak ada data unit.</p>
                    </div>
                </td>
            </tr>
        @endforelse

        @if($units->hasPages())
            <x-slot name="pagination">
                {{ $units->withQueryString()->links() }}
            </x-slot>
        @endif
    </x-table>
</div>

{{-- Create Modal --}}
<x-modal name="createUnitModal" maxWidth="xl">
    <x-slot name="header">
        <h3 class="text-lg font-semibold text-[var(--text-primary)]">Tambah Unit</h3>
    </x-slot>

    <form action="{{ route('master.units.store') }}" method="POST" class="space-y-4">
        @csrf
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-[var(--text-primary)]">Direktorat <span class="text-red-500" aria-hidden="true">*</span></label>
            <select class="glass-input" name="directorate_id" required>
                <option value="">Pilih Direktorat</option>
                @foreach($directorates as $directorate)
                    <option value="{{ $directorate->id }}">{{ $directorate->name }}</option>
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
            <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'createUnitModal')">Batal</x-button>
            <x-button type="submit">Simpan</x-button>
        </div>
    </form>
</x-modal>

{{-- Edit Modal --}}
<x-modal name="editUnitModal" maxWidth="xl">
    <x-slot name="header">
        <h3 class="text-lg font-semibold text-[var(--text-primary)]">Edit Unit</h3>
    </x-slot>

    <form id="editForm" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-[var(--text-primary)]">Direktorat <span class="text-red-500" aria-hidden="true">*</span></label>
            <select class="glass-input" name="directorate_id" id="edit_directorate_id" required>
                <option value="">Pilih Direktorat</option>
                @foreach($directorates as $directorate)
                    <option value="{{ $directorate->id }}">{{ $directorate->name }}</option>
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
            <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'editUnitModal')">Batal</x-button>
            <x-button type="submit">Simpan</x-button>
        </div>
    </form>
</x-modal>
@endsection

@push('scripts')
<script>
function editItem(item) {
    document.getElementById('editForm').action = '/master/units/' + item.id;
    document.getElementById('edit_directorate_id').value = item.directorate_id;
    document.getElementById('edit_code').value = item.code;
    document.getElementById('edit_name').value = item.name;
    document.getElementById('edit_description').value = item.description || '';
    document.getElementById('edit_sort_order').value = item.sort_order;
    document.getElementById('edit_is_active').checked = item.is_active;
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editUnitModal' }));
}
</script>
@endpush
