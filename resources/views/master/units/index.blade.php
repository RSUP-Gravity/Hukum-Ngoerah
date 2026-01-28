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
@php
    $hasFilters = request()->filled('search') || request()->filled('directorate_id') || request()->filled('active');
    $resultFrom = $units->firstItem() ?? 0;
    $resultTo = $units->lastItem() ?? 0;
    $selectedDirectorate = $directorates->firstWhere('id', (int) request('directorate_id'));
@endphp
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
    <x-glass-card :hover="false" class="p-6 overflow-visible relative z-20">
        <form action="{{ route('master.units.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-4 space-y-1.5">
                    <label class="text-sm font-medium text-[var(--text-primary)]">Pencarian</label>
                    <div class="relative">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]"></i>
                        <input type="text" class="glass-input pl-10" name="search"
                               value="{{ request('search') }}" placeholder="Cari nama atau kode unit...">
                    </div>
                </div>
                <div class="lg:col-span-3">
                    <x-select
                        name="directorate_id"
                        label="Direktorat"
                        placeholder="Semua Direktorat"
                        :options="$directorates->pluck('name', 'id')"
                        :value="request('directorate_id')"
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
                <div class="lg:col-span-3 flex flex-wrap items-end gap-2 lg:justify-end">
                    <x-button type="submit" size="sm">
                        <i class="bi bi-funnel"></i>
                        Terapkan
                    </x-button>
                    <x-button href="{{ route('master.units.index') }}" size="sm" variant="secondary" class="{{ $hasFilters ? '' : 'pointer-events-none opacity-60' }}">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Reset
                    </x-button>
                </div>
            </div>

            @if($hasFilters)
                <div class="flex flex-wrap items-center gap-2 border-t border-[var(--surface-glass-border)] pt-4">
                    <span class="text-xs font-semibold uppercase tracking-wide text-[var(--text-tertiary)]">Filter aktif</span>
                    @if(request()->filled('search'))
                        <a href="{{ route('master.units.index', request()->except('search', 'page')) }}"
                           class="badge-default inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs transition hover:bg-[var(--surface-glass-elevated)]">
                            <i class="bi bi-search text-[var(--text-tertiary)]"></i>
                            <span>{{ Str::limit(request('search'), 24) }}</span>
                            <i class="bi bi-x-lg text-[var(--text-tertiary)]"></i>
                        </a>
                    @endif
                    @if(request()->filled('directorate_id'))
                        <a href="{{ route('master.units.index', request()->except('directorate_id', 'page')) }}"
                           class="badge-default inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs transition hover:bg-[var(--surface-glass-elevated)]">
                            <i class="bi bi-diagram-3 text-[var(--text-tertiary)]"></i>
                            <span>Direktorat: {{ $selectedDirectorate?->name ?? 'Tidak dikenal' }}</span>
                            <i class="bi bi-x-lg text-[var(--text-tertiary)]"></i>
                        </a>
                    @endif
                    @if(request()->filled('active'))
                        <a href="{{ route('master.units.index', request()->except('active', 'page')) }}"
                           class="badge-default inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs transition hover:bg-[var(--surface-glass-elevated)]">
                            <i class="bi bi-toggle-on text-[var(--text-tertiary)]"></i>
                            <span>Status: {{ request('active') === '1' ? 'Aktif' : 'Nonaktif' }}</span>
                            <i class="bi bi-x-lg text-[var(--text-tertiary)]"></i>
                        </a>
                    @endif
                    <a href="{{ route('master.units.index') }}" class="text-xs text-[var(--text-tertiary)] hover:text-[var(--text-primary)]">
                        Reset semua
                    </a>
                </div>
            @endif

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-[var(--surface-glass-border)] pt-4 text-xs text-[var(--text-tertiary)]">
                <span>Menampilkan {{ $resultFrom }}-{{ $resultTo }} dari {{ $units->total() }} data</span>
                <span>Urutan: sort order, nama</span>
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
                    <div class="text-xs text-[var(--text-tertiary)]">{{ $unit->users_count ?? 0 }} pengguna</div>
                </td>
                <td>
                    <x-badge type="default" size="sm">{{ $unit->directorate->name ?? '-' }}</x-badge>
                </td>
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
                            <x-button type="button" size="sm" variant="secondary" onclick="editItem(@js($unit))" aria-label="Edit unit" title="Edit unit">
                                <i class="bi bi-pencil"></i>
                            </x-button>
                        @endcan
                        @can('master.delete')
                            <form action="{{ route('master.units.destroy', $unit) }}" method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus?')">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" size="sm" variant="danger" aria-label="Hapus unit" title="Hapus unit">
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
                        @can('master.create')
                            <x-button type="button" size="sm" @click="$dispatch('open-modal', 'createUnitModal')">
                                Tambah Unit
                            </x-button>
                        @endcan
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
        <x-select
            name="directorate_id"
            label="Direktorat"
            placeholder="Pilih Direktorat"
            :options="$directorates->pluck('name', 'id')"
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
        <x-select
            name="directorate_id"
            id="edit_directorate_id"
            label="Direktorat"
            placeholder="Pilih Direktorat"
            :options="$directorates->pluck('name', 'id')"
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
