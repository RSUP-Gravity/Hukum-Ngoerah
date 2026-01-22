@extends('layouts.app')

@section('title', 'Bandingkan Versi - ' . Str::limit($document->title, 30))

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Dokumen', 'url' => route('documents.index')],
        ['label' => Str::limit($document->title, 25), 'url' => route('documents.show', $document)],
        ['label' => 'Bandingkan Versi']
    ]" />
@endsection

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Bandingkan Versi</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $document->document_number }} • {{ $document->title }}</p>
        </div>
        <x-button href="{{ route('documents.show', $document) }}" variant="secondary">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </x-button>
    </div>

    {{-- Version Comparison Header --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <x-glass-card :hover="false" class="p-5 border-l-4 border-amber-400">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <x-badge type="warning" size="sm">Versi Lama</x-badge>
                    <h2 class="mt-2 text-lg font-semibold text-[var(--text-primary)]">Versi {{ $version1->version_number }}</h2>
                    <p class="mt-2 text-xs text-[var(--text-tertiary)]">
                        <i class="bi bi-calendar"></i>
                        {{ $version1->created_at->format('d M Y, H:i') }}
                    </p>
                    <p class="text-xs text-[var(--text-tertiary)]">
                        <i class="bi bi-person"></i>
                        {{ $version1->uploader->name ?? 'System' }}
                    </p>
                </div>
                <x-button href="{{ route('documents.download', [$document, $version1]) }}" size="sm" variant="secondary">
                    <i class="bi bi-download"></i>
                    Download
                </x-button>
            </div>
        </x-glass-card>

        <x-glass-card :hover="false" class="p-5 border-l-4 border-emerald-400">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <x-badge type="success" size="sm">Versi Baru</x-badge>
                    <h2 class="mt-2 text-lg font-semibold text-[var(--text-primary)]">Versi {{ $version2->version_number }}</h2>
                    <p class="mt-2 text-xs text-[var(--text-tertiary)]">
                        <i class="bi bi-calendar"></i>
                        {{ $version2->created_at->format('d M Y, H:i') }}
                    </p>
                    <p class="text-xs text-[var(--text-tertiary)]">
                        <i class="bi bi-person"></i>
                        {{ $version2->uploader->name ?? 'System' }}
                    </p>
                </div>
                <x-button href="{{ route('documents.download', [$document, $version2]) }}" size="sm" variant="secondary">
                    <i class="bi bi-download"></i>
                    Download
                </x-button>
            </div>
        </x-glass-card>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Changes Summary --}}
        <div class="lg:col-span-8 space-y-6">
            <x-glass-card :hover="false" class="p-6">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]">
                    <i class="bi bi-arrow-left-right"></i>
                    Ringkasan Perubahan
                </h3>
                <div class="mt-4">
                    @if(count($metadataDiff) > 0)
                        <x-table>
                            <x-slot name="header">
                                <th class="w-3/12">Atribut</th>
                                <th class="w-4/12">Versi {{ $version1->version_number }}</th>
                                <th class="w-4/12">Versi {{ $version2->version_number }}</th>
                            </x-slot>

                            @if(isset($metadataDiff['file_size']))
                                <tr>
                                    <td class="text-sm font-medium text-[var(--text-primary)]">Ukuran File</td>
                                    <td class="text-sm text-amber-300 bg-amber-500/10">{{ $metadataDiff['file_size']['old'] }}</td>
                                    <td class="text-sm text-emerald-300 bg-emerald-500/10">
                                        {{ $metadataDiff['file_size']['new'] }}
                                        @if($metadataDiff['file_size']['change'] === 'increased')
                                            <i class="bi bi-arrow-up text-emerald-400"></i>
                                        @else
                                            <i class="bi bi-arrow-down text-rose-400"></i>
                                        @endif
                                    </td>
                                </tr>
                            @endif

                            @if(isset($metadataDiff['filename']))
                                <tr>
                                    <td class="text-sm font-medium text-[var(--text-primary)]">Nama File</td>
                                    <td class="text-sm text-amber-300 bg-amber-500/10">{{ $metadataDiff['filename']['old'] }}</td>
                                    <td class="text-sm text-emerald-300 bg-emerald-500/10">{{ $metadataDiff['filename']['new'] }}</td>
                                </tr>
                            @endif

                            @if(isset($metadataDiff['file_content']))
                                <tr>
                                    <td class="text-sm font-medium text-[var(--text-primary)]">Konten File</td>
                                    <td colspan="2" class="text-center text-sm text-cyan-300">
                                        <i class="bi bi-file-diff"></i>
                                        {{ $metadataDiff['file_content']['message'] }}
                                    </td>
                                </tr>
                            @endif
                        </x-table>
                    @else
                        <div class="py-6 text-center text-[var(--text-tertiary)]">
                            <i class="bi bi-check-circle text-3xl text-emerald-400"></i>
                            <p class="mt-2 text-sm">Tidak ada perbedaan metadata yang terdeteksi.</p>
                        </div>
                    @endif
                </div>
            </x-glass-card>

            {{-- File Comparison --}}
            <x-glass-card :hover="false" class="p-6">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]">
                    <i class="bi bi-file-earmark-diff"></i>
                    Perbandingan File
                </h3>
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-amber-500/20 bg-amber-500/10 p-4">
                        <div class="flex items-center gap-3">
                            @php
                                $icon1 = match($version1->file_extension ?? 'pdf') {
                                    'pdf' => 'bi-file-earmark-pdf text-rose-400',
                                    'doc', 'docx' => 'bi-file-earmark-word text-sky-400',
                                    default => 'bi-file-earmark text-[var(--text-tertiary)]',
                                };
                            @endphp
                            <i class="bi {{ $icon1 }} text-2xl"></i>
                            <div>
                                <div class="text-sm font-semibold text-[var(--text-primary)]">{{ $version1->original_filename }}</div>
                                <div class="text-xs text-[var(--text-tertiary)]">{{ $version1->file_size_formatted ?? number_format($version1->file_size / 1024, 0) . ' KB' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4">
                        <div class="flex items-center gap-3">
                            @php
                                $icon2 = match($version2->file_extension ?? 'pdf') {
                                    'pdf' => 'bi-file-earmark-pdf text-rose-400',
                                    'doc', 'docx' => 'bi-file-earmark-word text-sky-400',
                                    default => 'bi-file-earmark text-[var(--text-tertiary)]',
                                };
                            @endphp
                            <i class="bi {{ $icon2 }} text-2xl"></i>
                            <div>
                                <div class="text-sm font-semibold text-[var(--text-primary)]">{{ $version2->original_filename }}</div>
                                <div class="text-xs text-[var(--text-tertiary)]">{{ $version2->file_size_formatted ?? number_format($version2->file_size / 1024, 0) . ' KB' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-alert mt-4 flex items-start gap-3 p-4 text-sm text-[var(--text-secondary)]">
                    <i class="bi bi-info-circle text-cyan-400"></i>
                    <p>Untuk perbandingan konten detail, silakan download kedua file dan gunakan tool pembanding dokumen eksternal.</p>
                </div>
            </x-glass-card>

            {{-- Change Notes --}}
            @if($version2->change_notes)
            <x-glass-card :hover="false" class="p-6">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]">
                    <i class="bi bi-sticky"></i>
                    Catatan Perubahan
                </h3>
                <p class="mt-3 text-sm text-[var(--text-secondary)]">{{ $version2->change_notes }}</p>
            </x-glass-card>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="lg:col-span-4 space-y-6">
            {{-- Activity Between Versions --}}
            <x-glass-card :hover="false" class="p-6">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]">
                    <i class="bi bi-clock-history"></i>
                    Aktivitas
                </h3>
                <div class="mt-4 max-h-96 divide-y divide-[var(--surface-glass-border)] overflow-y-auto">
                    @forelse($history as $item)
                        <div class="py-3">
                            <div class="flex justify-between gap-3 text-sm">
                                <div>
                                    <div class="font-semibold text-[var(--text-primary)]">{{ $item->action_label }}</div>
                                    @if($item->description)
                                        <div class="text-xs text-[var(--text-tertiary)]">{{ $item->description }}</div>
                                    @endif
                                </div>
                                <div class="text-right text-xs text-[var(--text-tertiary)]">
                                    <div>{{ $item->performer->name ?? 'System' }}</div>
                                    <div>{{ $item->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-sm text-[var(--text-tertiary)]">
                            Tidak ada aktivitas antara dua versi ini
                        </div>
                    @endforelse
                </div>
            </x-glass-card>

            {{-- Quick Actions --}}
            @can('documents.edit')
            <x-glass-card :hover="false" class="p-6">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]">Aksi</h3>
                <div class="mt-4 space-y-2">
                    @if(auth()->user()->isAdmin() && !$version1->is_current)
                    <form action="{{ route('documents.restore-version', [$document, $version1]) }}" method="POST"
                          onsubmit="return confirm('Pulihkan versi {{ $version1->version_number }}? Ini akan membuat versi baru berdasarkan versi tersebut.');">
                        @csrf
                        <x-button type="submit" variant="secondary" class="w-full">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            Pulihkan Versi {{ $version1->version_number }}
                        </x-button>
                    </form>
                    @endif

                    <x-button href="{{ route('documents.show', $document) }}" variant="secondary" class="w-full">
                        <i class="bi bi-arrow-left"></i>
                        Kembali ke Dokumen
                    </x-button>
                </div>
            </x-glass-card>
            @endcan
        </div>
    </div>
</div>
@endsection
