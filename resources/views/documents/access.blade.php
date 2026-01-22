@extends('layouts.app')

@section('title', 'Akses Dokumen')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Dokumen', 'url' => route('documents.index')],
        ['label' => $document->title, 'url' => route('documents.show', $document)],
        ['label' => 'Manajemen Akses']
    ]" />
@endsection

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Manajemen Akses Dokumen</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $document->document_number }} - {{ Str::limit($document->title, 50) }}</p>
        </div>
        <x-button href="{{ route('documents.show', $document) }}" variant="secondary">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </x-button>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Add Access Form --}}
        <x-glass-card :hover="false" class="p-6">
            <h2 class="text-lg font-semibold text-[var(--text-primary)]">Tambah Akses</h2>

            <form action="{{ route('documents.access.store', $document) }}" method="POST" class="mt-5 space-y-4">
                @csrf

                <div class="space-y-1.5">
                    <label class="block text-sm font-medium text-[var(--text-primary)]">Tipe Akses</label>
                    <select name="access_type" id="accessType" class="glass-input" onchange="toggleAccessTarget()">
                        <option value="user">Pengguna Spesifik</option>
                        <option value="unit">Unit/Bagian</option>
                        <option value="role">Role/Peran</option>
                    </select>
                </div>

                {{-- User Selection --}}
                <div id="userSelect" class="space-y-1.5">
                    <label class="block text-sm font-medium text-[var(--text-primary)]">Pilih Pengguna</label>
                    <select name="user_id" class="glass-input">
                        <option value="">-- Pilih Pengguna --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->nip }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Unit Selection --}}
                <div id="unitSelect" class="space-y-1.5 hidden">
                    <label class="block text-sm font-medium text-[var(--text-primary)]">Pilih Unit</label>
                    <select name="unit_id" class="glass-input">
                        <option value="">-- Pilih Unit --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Role Selection --}}
                <div id="roleSelect" class="space-y-1.5 hidden">
                    <label class="block text-sm font-medium text-[var(--text-primary)]">Pilih Role</label>
                    <select name="role_id" class="glass-input">
                        <option value="">-- Pilih Role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->display_name ?? $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-sm font-medium text-[var(--text-primary)]">Level Akses</label>
                    <select name="access_level" class="glass-input">
                        <option value="view">Baca Saja</option>
                        <option value="download">Baca & Download</option>
                        <option value="edit">Baca, Download & Edit</option>
                        <option value="full">Akses Penuh</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-sm font-medium text-[var(--text-primary)]">Berlaku Sampai</label>
                    <input type="date" name="expires_at" class="glass-input" min="{{ now()->addDay()->format('Y-m-d') }}">
                    <p class="text-xs text-[var(--text-tertiary)]">Kosongkan jika tidak ada batas waktu</p>
                </div>

                <x-button type="submit" class="w-full">
                    <i class="bi bi-plus-lg"></i>
                    Tambah Akses
                </x-button>
            </form>
        </x-glass-card>

        {{-- Access List --}}
        <div class="space-y-6 lg:col-span-2">
            <x-glass-card :hover="false" class="p-0">
                <div class="px-6 py-5">
                    <h2 class="text-lg font-semibold text-[var(--text-primary)]">Daftar Akses</h2>
                </div>
                <x-table>
                    <x-slot name="header">
                        <th>Akses Untuk</th>
                        <th>Level</th>
                        <th>Diberikan Oleh</th>
                        <th>Berlaku Sampai</th>
                        <th class="text-right">Aksi</th>
                    </x-slot>

                    @forelse($accesses as $access)
                        @php
                            $levelType = match($access->permission) {
                                'full' => 'success',
                                'edit' => 'info',
                                'download' => 'attention',
                                default => 'default'
                            };
                            $levelLabel = match($access->permission) {
                                'full' => 'Akses Penuh',
                                'edit' => 'Baca, Download & Edit',
                                'download' => 'Baca & Download',
                                default => 'Baca Saja'
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    @if($access->user_id)
                                        <i class="bi bi-person text-primary-500"></i>
                                        <span class="text-sm text-[var(--text-primary)]">{{ $access->user->name ?? '-' }}</span>
                                        <x-badge type="default" size="sm">Pengguna</x-badge>
                                    @elseif($access->unit_id)
                                        <i class="bi bi-building text-emerald-500"></i>
                                        <span class="text-sm text-[var(--text-primary)]">{{ $access->unit->name ?? '-' }}</span>
                                        <x-badge type="default" size="sm">Unit</x-badge>
                                    @elseif($access->role_id)
                                        <i class="bi bi-shield text-amber-500"></i>
                                        <span class="text-sm text-[var(--text-primary)]">{{ $access->role->display_name ?? $access->role->name ?? '-' }}</span>
                                        <x-badge type="default" size="sm">Role</x-badge>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <x-badge :type="$levelType" size="sm">{{ $levelLabel }}</x-badge>
                            </td>
                            <td>
                                <div class="text-sm text-[var(--text-primary)]">{{ $access->grantedBy->name ?? '-' }}</div>
                                <div class="text-xs text-[var(--text-tertiary)]">{{ $access->created_at->format('d M Y') }}</div>
                            </td>
                            <td>
                                @if($access->valid_until)
                                    @if($access->valid_until->isPast())
                                        <x-badge type="expired" size="sm">Kadaluarsa</x-badge>
                                    @else
                                        <span class="text-sm text-[var(--text-primary)]">{{ $access->valid_until->format('d M Y') }}</span>
                                    @endif
                                @else
                                    <span class="text-sm text-[var(--text-tertiary)]">Tidak terbatas</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <form action="{{ route('documents.access.destroy', [$document, $access]) }}" method="POST"
                                      onsubmit="return confirm('Yakin ingin mencabut akses ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" size="sm" variant="danger">
                                        <i class="bi bi-x-lg"></i>
                                    </x-button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center">
                                <div class="space-y-2 text-[var(--text-tertiary)]">
                                    <i class="bi bi-lock text-3xl opacity-40"></i>
                                    <p class="text-sm">Belum ada akses khusus yang diberikan.</p>
                                    <p class="text-xs">Dokumen ini hanya dapat diakses oleh pemilik dan administrator.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </x-table>
            </x-glass-card>

            {{-- Access Info --}}
            <x-glass-card :hover="false" class="p-6">
                <div class="flex items-center gap-2">
                    <i class="bi bi-info-circle text-primary-500"></i>
                    <h3 class="text-sm font-semibold text-[var(--text-primary)]">Informasi Akses</h3>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-3">
                        <h6 class="text-xs font-semibold text-[var(--text-secondary)]">Baca Saja (view)</h6>
                        <p class="mt-2 text-xs text-[var(--text-tertiary)]">Pengguna hanya dapat melihat informasi dokumen tanpa dapat mengunduh.</p>
                    </div>
                    <div class="rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-3">
                        <h6 class="text-xs font-semibold text-[var(--text-secondary)]">Baca & Download</h6>
                        <p class="mt-2 text-xs text-[var(--text-tertiary)]">Pengguna dapat melihat dan mengunduh file dokumen.</p>
                    </div>
                    <div class="rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-3">
                        <h6 class="text-xs font-semibold text-[var(--text-secondary)]">Baca, Download & Edit</h6>
                        <p class="mt-2 text-xs text-[var(--text-tertiary)]">Pengguna dapat melihat, mengunduh, dan mengedit dokumen.</p>
                    </div>
                    <div class="rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-3">
                        <h6 class="text-xs font-semibold text-[var(--text-secondary)]">Akses Penuh</h6>
                        <p class="mt-2 text-xs text-[var(--text-tertiary)]">Pengguna memiliki semua akses termasuk mengelola akses dokumen.</p>
                    </div>
                </div>
            </x-glass-card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleAccessTarget() {
    const type = document.getElementById('accessType').value;
    
    document.getElementById('userSelect').classList.add('hidden');
    document.getElementById('unitSelect').classList.add('hidden');
    document.getElementById('roleSelect').classList.add('hidden');
    
    if (type === 'user') {
        document.getElementById('userSelect').classList.remove('hidden');
    } else if (type === 'unit') {
        document.getElementById('unitSelect').classList.remove('hidden');
    } else if (type === 'role') {
        document.getElementById('roleSelect').classList.remove('hidden');
    }
}
</script>
@endpush
