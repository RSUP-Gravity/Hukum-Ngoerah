@extends('layouts.app')

@section('title', $document->title)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Dokumen', 'url' => route('documents.index')],
        ['label' => Str::limit($document->title, 30)]
    ]" />
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Content --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Document Header --}}
        <x-glass-card :hover="false" class="p-6 overflow-visible z-30">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        @php
                            $statusTypes = [
                                'draft' => 'default',
                                'pending_review' => 'attention',
                                'pending_approval' => 'warning',
                                'approved' => 'active',
                                'published' => 'success',
                                'expired' => 'expired',
                                'archived' => 'default',
                                'rejected' => 'expired',
                            ];
                            $statusLabels = App\Models\Document::STATUSES;
                            $expiryInfo = app(\App\Services\DocumentStatusService::class)->getExpiryInfo($document);
                            $expiryLabelMap = [
                                \App\Services\DocumentStatusService::STATUS_ATTENTION => 'Perhatian',
                                \App\Services\DocumentStatusService::STATUS_WARNING => 'Peringatan',
                                \App\Services\DocumentStatusService::STATUS_CRITICAL => 'Kritis',
                                \App\Services\DocumentStatusService::STATUS_EXPIRED => 'Kedaluwarsa',
                            ];
                        @endphp
                        <x-badge :type="$statusTypes[$document->status] ?? 'default'" size="lg">
                            {{ $statusLabels[$document->status] ?? $document->status }}
                        </x-badge>
                        @if(isset($expiryLabelMap[$expiryInfo['status']]))
                            <x-badge :type="$expiryInfo['status']">{{ $expiryLabelMap[$expiryInfo['status']] }}</x-badge>
                        @endif
                        <x-badge type="default">{{ $document->type->name ?? '-' }}</x-badge>
                    </div>

                    <h1 class="text-2xl font-semibold text-[var(--text-primary)]">{{ $document->title }}</h1>

                    <div class="flex flex-wrap items-center gap-2 text-sm text-[var(--text-tertiary)]">
                        <span>{{ $document->document_number }}</span>
                        <span class="text-[var(--surface-glass-border)]">•</span>
                        <span>{{ $document->document_date?->format('d F Y') }}</span>
                        @if($document->category)
                            <span class="text-[var(--surface-glass-border)]">•</span>
                            <span>{{ $document->category->name }}</span>
                        @endif
                    </div>
                </div>

                {{-- Action Buttons --}}
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="p-2 rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:border-[var(--surface-glass-border-hover)] transition-colors" aria-label="Menu aksi dokumen">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.75a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 7.5a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 7.5a1.5 1.5 0 110-3 1.5 1.5 0 010 3z" />
                            </svg>
                        </button>
                    </x-slot>

                    @if($document->currentVersion)
                        <x-dropdown-item href="{{ route('documents.download', $document) }}">
                            Download
                        </x-dropdown-item>
                        <x-dropdown-item href="{{ route('documents.print', $document) }}" target="_blank">
                            Print
                        </x-dropdown-item>
                    @endif

                    @can('documents.edit')
                        @if($document->isEditable())
                            <div class="border-t border-[var(--surface-glass-border)] my-1"></div>
                            <x-dropdown-item href="{{ route('documents.edit', $document) }}">
                                Edit
                            </x-dropdown-item>
                        @endif
                    @endcan
                </x-dropdown>
            </div>
        </x-glass-card>

        {{-- Document Content --}}
        <x-glass-card :hover="false" class="p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-[var(--text-primary)]">Informasi Dokumen</h2>
            </div>

            <div class="mt-5 space-y-5 text-sm">
                @if($document->parties)
                    <div>
                        <h3 class="text-sm font-semibold text-[var(--text-secondary)]">Pihak-pihak Terkait</h3>
                        <p class="mt-1 text-[var(--text-primary)]">{{ $document->parties }}</p>
                    </div>
                @endif

                @if($document->description)
                    <div>
                        <h3 class="text-sm font-semibold text-[var(--text-secondary)]">Deskripsi</h3>
                        <p class="mt-1 text-[var(--text-primary)]">{!! nl2br(e($document->description)) !!}</p>
                    </div>
                @endif

                @if($document->notes)
                    <div>
                        <h3 class="text-sm font-semibold text-[var(--text-secondary)]">Catatan Internal</h3>
                        <x-alert type="info" :dismissible="false" class="mt-2">
                            {!! nl2br(e($document->notes)) !!}
                        </x-alert>
                    </div>
                @endif
                    
                @if($document->rejection_reason)
                    <div>
                        <h3 class="text-sm font-semibold text-red-600 dark:text-red-400">Alasan Penolakan</h3>
                        <x-alert type="error" :dismissible="false" class="mt-2">
                            {!! nl2br(e($document->rejection_reason)) !!}
                        </x-alert>
                    </div>
                @endif
            </div>
        </x-glass-card>
            
            {{-- Versions --}}
            <x-glass-card :hover="false" class="p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="text-lg font-semibold text-[var(--text-primary)]">Versi Dokumen</h2>
                    @can('documents.edit')
                        @if($document->isEditable())
                            <x-button type="button" size="sm" variant="secondary" x-on:click="$dispatch('open-modal', 'uploadVersionModal')">
                                Unggah Versi Baru
                            </x-button>
                        @endif
                    @endcan
                </div>

                <div class="mt-5 divide-y divide-[var(--surface-glass-border)]">
                    @forelse($document->versions as $index => $version)
                        <div class="flex flex-col gap-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-start gap-4">
                                <div class="mt-1">
                                    @php
                                        $iconClass = match(strtolower($version->file_type ?? 'pdf')) {
                                            'pdf' => 'bi-file-earmark-pdf text-red-500',
                                            'doc', 'docx' => 'bi-file-earmark-word text-primary-500',
                                            default => 'bi-file-earmark text-[var(--text-tertiary)]',
                                        };
                                    @endphp
                                    <i class="bi {{ $iconClass }} text-2xl"></i>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex flex-wrap items-center gap-2 text-sm font-semibold text-[var(--text-primary)]">
                                        <span>Versi {{ $version->version_number }}</span>
                                        @if($version->version_number === $document->current_version)
                                            <x-badge type="success" size="sm">Terbaru</x-badge>
                                        @endif
                                    </div>
                                    <div class="text-sm text-[var(--text-tertiary)]">
                                        {{ $version->file_name }} •
                                        {{ number_format($version->file_size / 1024, 0) }} KB •
                                        {{ $version->created_at->format('d/m/Y H:i') }}
                                    </div>
                                    @if($version->change_summary)
                                        <div class="text-xs text-[var(--text-tertiary)]">{{ $version->change_summary }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                {{-- Compare with previous version --}}
                                @if($document->versions->count() > 1 && $version->version_number > 1)
                                    @php
                                        $prevVersion = $document->versions->firstWhere('version_number', $version->version_number - 1);
                                    @endphp
                                    @if($prevVersion)
                                        <a href="{{ route('documents.compare-versions', [$document, $prevVersion, $version]) }}"
                                           class="btn-ghost px-2 py-2 rounded-lg"
                                           title="Bandingkan dengan versi sebelumnya">
                                            <i class="bi bi-arrow-left-right text-base"></i>
                                        </a>
                                    @endif
                                @endif

                                {{-- Restore version (Admin only, not current) --}}
                                @if(auth()->user()->isAdmin() && $version->version_number !== $document->current_version)
                                    <form action="{{ route('documents.restore-version', [$document, $version]) }}" method="POST"
                                          onsubmit="return confirm('Pulihkan versi {{ $version->version_number }}?');">
                                        @csrf
                                        <button type="submit" class="btn-ghost px-2 py-2 rounded-lg" title="Pulihkan versi ini">
                                            <i class="bi bi-arrow-counterclockwise text-base"></i>
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('documents.download', [$document, $version]) }}" class="btn-secondary px-2 py-2 rounded-lg" title="Download">
                                    <i class="bi bi-download text-base"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center text-sm text-[var(--text-tertiary)]">
                            Belum ada file yang diunggah
                        </div>
                    @endforelse
                </div>
            </x-glass-card>

            {{-- History --}}
            <x-glass-card :hover="false" class="p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-[var(--text-primary)]">Riwayat Aktivitas</h2>
                </div>

                <div class="mt-5 max-h-[400px] space-y-4 overflow-y-auto pr-2 custom-scrollbar">
                    @forelse($document->history as $history)
                        <div class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-[var(--text-primary)]">
                                        {{ $history->action_label ?? $history->action }}
                                    </div>
                                    @if($history->description)
                                        <div class="text-xs text-[var(--text-tertiary)]">{{ $history->description }}</div>
                                    @endif
                                </div>
                                <div class="text-sm text-[var(--text-tertiary)] sm:text-right">
                                    <div>{{ $history->performer->name ?? 'System' }}</div>
                                    <div class="text-xs">{{ $history->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center text-sm text-[var(--text-tertiary)]">
                            Belum ada riwayat aktivitas
                        </div>
                    @endforelse
                </div>
            </x-glass-card>
        </div>
        
        {{-- Sidebar --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Workflow Actions --}}
            @if($document->status !== 'archived')
            <x-glass-card :hover="false" class="p-6">
                <h2 class="text-lg font-semibold text-[var(--text-primary)]">Aksi</h2>
                <div class="mt-4 space-y-2">
                        {{-- Submit for Review --}}
                        @if($document->canSubmitForReview())
                        @can('documents.create')
                        <form action="{{ route('documents.submit-review', $document) }}" method="POST">
                            @csrf
                            <x-button type="submit" class="w-full">
                                Ajukan untuk Review
                            </x-button>
                        </form>
                        @endcan
                        @endif
                        
                        {{-- Submit for Approval --}}
                        @if($document->status === 'pending_review')
                        @can('documents.approve')
                        <x-button type="button" class="w-full" x-on:click="$dispatch('open-modal', 'submitApprovalModal')">
                            Ajukan untuk Approval
                        </x-button>
                        <x-button type="button" variant="danger" class="w-full" x-on:click="$dispatch('open-modal', 'rejectModal')">
                            Tolak
                        </x-button>
                        @endcan
                        @endif
                        
                        {{-- Approve/Reject --}}
                        @if($document->status === 'pending_approval')
                        @php
                            $pendingApproval = $document->approvals()->where('approver_id', auth()->id())->where('status', 'pending')->first();
                        @endphp
                        @if($pendingApproval)
                        <x-button type="button" class="w-full" x-on:click="$dispatch('open-modal', 'approveModal')">
                            Setujui
                        </x-button>
                        <x-button type="button" variant="danger" class="w-full" x-on:click="$dispatch('open-modal', 'rejectModal')">
                            Tolak
                        </x-button>
                        @endif
                        @endif
                        
                        {{-- Publish --}}
                        @if($document->status === 'approved')
                        @can('documents.publish')
                        <form action="{{ route('documents.publish', $document) }}" method="POST">
                            @csrf
                            <x-button type="submit" class="w-full">
                                Publikasikan
                            </x-button>
                        </form>
                        @endcan
                        @endif
                        
                        {{-- Archive --}}
                        @if(in_array($document->status, ['published', 'expired']))
                        @can('documents.archive')
                        <form action="{{ route('documents.archive', $document) }}" method="POST" onsubmit="return confirm('Yakin ingin mengarsipkan dokumen ini?')">
                            @csrf
                            <x-button type="submit" variant="secondary" class="w-full">
                                Arsipkan
                            </x-button>
                        </form>
                        @endcan
                        @endif
                </div>
            </x-glass-card>
            @endif
            
            {{-- Approval Status --}}
            @if($document->approvals->isNotEmpty())
            <x-glass-card :hover="false" class="p-6">
                <h2 class="text-lg font-semibold text-[var(--text-primary)]">Status Approval</h2>
                <div class="mt-4 divide-y divide-[var(--surface-glass-border)]">
                    @foreach($document->approvals as $approval)
                        @php
                            $approvalStatusType = match($approval->status) {
                                'approved' => 'success',
                                'rejected' => 'expired',
                                default => 'warning',
                            };
                            $approvalStatusLabel = match($approval->status) {
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                default => 'Menunggu',
                            };
                        @endphp
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <div class="text-sm font-semibold text-[var(--text-primary)]">{{ $approval->approver->name ?? '-' }}</div>
                                <div class="text-xs text-[var(--text-tertiary)]">Level {{ $approval->sequence }}</div>
                            </div>
                            <x-badge :type="$approvalStatusType">{{ $approvalStatusLabel }}</x-badge>
                        </div>
                    @endforeach
                </div>
            </x-glass-card>
            @endif
            
            {{-- Document Details --}}
            <x-glass-card :hover="false" class="p-6">
                <h2 class="text-lg font-semibold text-[var(--text-primary)]">Detail</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">Unit</dt>
                        <dd class="text-right text-[var(--text-primary)]">{{ $document->unit->name ?? '-' }}</dd>
                    </div>

                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">Kerahasiaan</dt>
                        <dd>
                            @php
                                $confTypes = ['public' => 'success', 'internal' => 'info', 'confidential' => 'warning', 'restricted' => 'critical'];
                                $confLabels = App\Models\Document::CONFIDENTIALITIES;
                            @endphp
                            <x-badge :type="$confTypes[$document->confidentiality] ?? 'default'">
                                {{ $confLabels[$document->confidentiality] ?? $document->confidentiality }}
                            </x-badge>
                        </dd>
                    </div>

                    @if($document->effective_date)
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-[var(--text-secondary)]">Berlaku</dt>
                            <dd class="text-right text-[var(--text-primary)]">{{ $document->effective_date->format('d/m/Y') }}</dd>
                        </div>
                    @endif

                    @if($document->expiry_date)
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-[var(--text-secondary)]">Kedaluwarsa</dt>
                            <dd class="text-right text-[var(--text-primary)]">
                                {{ $document->expiry_date->format('d/m/Y') }}
                                @if(isset($expiryLabelMap[$expiryInfo['status']]))
                                    <x-badge :type="$expiryInfo['status']" size="sm">{{ $expiryLabelMap[$expiryInfo['status']] }}</x-badge>
                                @endif
                            </dd>
                        </div>
                    @endif

                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">Versi</dt>
                        <dd class="text-right text-[var(--text-primary)]">{{ $document->current_version }}</dd>
                    </div>

                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">Jumlah Unduhan</dt>
                        <dd class="flex items-center gap-2 text-right text-[var(--text-primary)]">
                            <i class="bi bi-download text-primary-500"></i>
                            <span class="font-semibold">{{ $document->formatted_download_count }}</span>
                            <span class="text-xs text-[var(--text-tertiary)]">kali</span>
                        </dd>
                    </div>

                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">Dibuat</dt>
                        <dd class="text-right text-[var(--text-primary)]">
                            <div>{{ $document->creator->name ?? '-' }}</div>
                            <div class="text-xs text-[var(--text-tertiary)]">{{ $document->created_at->format('d/m/Y H:i') }}</div>
                        </dd>
                    </div>

                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">Terakhir Update</dt>
                        <dd class="text-right text-[var(--text-primary)]">
                            <div>{{ $document->updater->name ?? '-' }}</div>
                            <div class="text-xs text-[var(--text-tertiary)]">{{ $document->updated_at->format('d/m/Y H:i') }}</div>
                        </dd>
                    </div>
                </dl>
            </x-glass-card>
            
            {{-- Access Management --}}
            @can('documents.manage_access')
            <x-glass-card :hover="false" class="p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-[var(--text-primary)]">Akses Khusus</h2>
                    <x-button href="{{ route('documents.access', $document) }}" size="sm" variant="secondary">
                        Kelola
                    </x-button>
                </div>

                <div class="mt-4 divide-y divide-[var(--surface-glass-border)]">
                    @forelse($document->accessPermissions->take(5) as $access)
                        <div class="flex items-center justify-between py-3 text-sm">
                            <span class="text-[var(--text-primary)]">{{ $access->user->name ?? '-' }}</span>
                            <x-badge type="default" size="sm">
                                {{ App\Models\DocumentAccess::PERMISSIONS[$access->permission] ?? $access->permission }}
                            </x-badge>
                        </div>
                    @empty
                        <div class="py-6 text-center text-sm text-[var(--text-tertiary)]">
                            Tidak ada akses khusus
                        </div>
                    @endforelse
                </div>
            </x-glass-card>
            @endcan
            
            {{-- Related Documents --}}
            @if($relatedDocuments->count() > 0)
            <x-glass-card :hover="false" class="p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-[var(--text-primary)]">Dokumen Terkait</h2>
                </div>

                <div class="mt-4 divide-y divide-[var(--surface-glass-border)]">
                    @foreach($relatedDocuments as $related)
                        <a href="{{ route('documents.show', $related) }}" class="block rounded-lg py-3 transition-colors hover:bg-[var(--surface-glass)]">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-[var(--text-primary)]" title="{{ $related->title }}">
                                        {{ Str::limit($related->title, 40) }}
                                    </div>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-[var(--text-tertiary)]">
                                        <x-badge type="default" size="sm">{{ $related->type->name ?? '-' }}</x-badge>
                                        @if($related->unit)
                                            <span>{{ Str::limit($related->unit->name, 15) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-xs text-[var(--text-tertiary)]">
                                    {{ $related->document_date?->format('d/m/Y') }}
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-4 text-center">
                    <x-button href="{{ route('documents.index', ['type' => $document->document_type_id]) }}" size="sm" variant="secondary">
                        Lihat Semua
                    </x-button>
                </div>
            </x-glass-card>
            @endif
        </div>
    </div>

{{-- Upload Version Modal --}}
<x-modal name="uploadVersionModal" maxWidth="xl">
    <x-slot name="header">
        <h3 class="text-lg font-semibold text-[var(--text-primary)]">Unggah Versi Baru</h3>
    </x-slot>

    <form action="{{ route('documents.upload-version', $document) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div class="space-y-2">
            <label class="text-sm font-medium text-[var(--text-primary)]">File <span class="text-red-500">*</span></label>
            <input type="file" class="glass-input file:mr-4 file:rounded-lg file:border-0 file:bg-[var(--surface-glass)] file:px-3 file:py-2 file:text-sm file:text-[var(--text-primary)]" name="file" accept=".pdf,.doc,.docx" required>
            <p class="text-xs text-[var(--text-tertiary)]">Format: PDF, DOC, DOCX. Maksimal 50MB</p>
        </div>
        <div class="space-y-2">
            <label class="text-sm font-medium text-[var(--text-primary)]">Catatan Perubahan</label>
            <textarea class="glass-input h-28" name="change_summary" rows="3" placeholder="Jelaskan perubahan pada versi ini..."></textarea>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-[var(--surface-glass-border)] pt-4 sm:flex-row sm:justify-end">
            <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'uploadVersionModal')">Batal</x-button>
            <x-button type="submit">Unggah</x-button>
        </div>
    </form>
</x-modal>

{{-- Approve Modal --}}
<x-modal name="approveModal" maxWidth="lg">
    <x-slot name="header">
        <h3 class="text-lg font-semibold text-[var(--text-primary)]">Setujui Dokumen</h3>
    </x-slot>

    <form action="{{ route('documents.approve', $document) }}" method="POST" class="space-y-4">
        @csrf
        <div class="space-y-2 text-sm text-[var(--text-secondary)]">
            <p>Anda akan menyetujui dokumen:</p>
            <p class="text-[var(--text-primary)] font-semibold">{{ $document->title }}</p>
        </div>
        <div class="space-y-2">
            <label class="text-sm font-medium text-[var(--text-primary)]">Catatan (opsional)</label>
            <textarea class="glass-input h-28" name="notes" rows="3" placeholder="Catatan approval..."></textarea>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-[var(--surface-glass-border)] pt-4 sm:flex-row sm:justify-end">
            <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'approveModal')">Batal</x-button>
            <x-button type="submit">Setujui</x-button>
        </div>
    </form>
</x-modal>

{{-- Reject Modal --}}
<x-modal name="rejectModal" maxWidth="lg">
    <x-slot name="header">
        <h3 class="text-lg font-semibold text-[var(--text-primary)]">Tolak Dokumen</h3>
    </x-slot>

    <form action="{{ route('documents.reject', $document) }}" method="POST" class="space-y-4">
        @csrf
        <div class="space-y-2 text-sm text-[var(--text-secondary)]">
            <p>Anda akan menolak dokumen:</p>
            <p class="text-[var(--text-primary)] font-semibold">{{ $document->title }}</p>
        </div>
        <div class="space-y-2">
            <label class="text-sm font-medium text-[var(--text-primary)]">Alasan Penolakan <span class="text-red-500">*</span></label>
            <textarea class="glass-input h-28" name="rejection_reason" rows="3" placeholder="Jelaskan alasan penolakan..." required></textarea>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-[var(--surface-glass-border)] pt-4 sm:flex-row sm:justify-end">
            <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'rejectModal')">Batal</x-button>
            <x-button type="submit" variant="danger">Tolak</x-button>
        </div>
    </form>
</x-modal>

{{-- Submit for Approval Modal --}}
<x-modal name="submitApprovalModal" maxWidth="4xl">
    <x-slot name="header">
        <h3 class="text-lg font-semibold text-[var(--text-primary)]">Ajukan untuk Approval</h3>
    </x-slot>

    <form
        action="{{ route('documents.submit-approval', $document) }}"
        method="POST"
        class="space-y-4"
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
        }"
    >
        @csrf
        <div class="space-y-2 text-sm text-[var(--text-secondary)]">
            <p>Dokumen ini akan diajukan untuk approval. Pilih approver dan urutannya.</p>
            <p class="text-xs text-[var(--text-tertiary)]">Urutan approval mengikuti daftar di sisi kanan.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
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

        <div class="flex flex-col-reverse gap-2 border-t border-[var(--surface-glass-border)] pt-4 sm:flex-row sm:justify-end">
            <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'submitApprovalModal')">Batal</x-button>
            <x-button type="submit" x-bind:disabled="selected.length === 0">Ajukan</x-button>
        </div>
    </form>
</x-modal>
@endsection
