@extends('layouts.app')

@section('title', 'Notifikasi')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Notifikasi']
    ]" />
@endsection

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Notifikasi</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">Kelola notifikasi dan pemberitahuan</p>
        </div>
        @if($notifications->where('is_read', false)->count() > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <x-button type="submit" variant="secondary">
                    <i class="bi bi-check-all"></i>
                    Tandai Semua Dibaca
                </x-button>
            </form>
        @endif
    </div>

    {{-- Filters --}}
    <x-glass-card :hover="false" class="p-4">
        <div class="flex flex-wrap gap-2">
            <x-button href="{{ route('notifications.index') }}" size="sm" :variant="!request('filter') ? 'primary' : 'secondary'">
                Semua
            </x-button>
                <x-button href="{{ route('notifications.index', ['filter' => 'unread']) }}" size="sm" :variant="request('filter') === 'unread' ? 'primary' : 'secondary'">
                Belum Dibaca
                @if($unreadCount > 0)
                    <x-badge type="critical" size="sm" class="ml-2">{{ $unreadCount }}</x-badge>
                @endif
            </x-button>
            <x-button href="{{ route('notifications.index', ['filter' => 'read']) }}" size="sm" :variant="request('filter') === 'read' ? 'primary' : 'secondary'">
                Sudah Dibaca
            </x-button>
        </div>
    </x-glass-card>

    {{-- Notification List --}}
    <x-glass-card :hover="false" class="divide-y divide-[var(--surface-glass-border)]">
        @forelse($notifications as $notification)
            @php
                $iconClass = match($notification->data['type'] ?? 'info') {
                    'document' => 'bi-file-earmark-text text-primary-400',
                    'approval' => 'bi-check-circle text-emerald-400',
                    'rejection' => 'bi-x-circle text-rose-400',
                    'warning' => 'bi-exclamation-triangle text-amber-400',
                    'expiry' => 'bi-clock text-amber-400',
                    default => 'bi-bell text-cyan-400'
                };
            @endphp
            <div class="p-4 transition hover:bg-[var(--surface-glass)] {{ !$notification->is_read ? 'bg-[var(--surface-glass)]' : '' }}">
                <div class="flex flex-col gap-4 sm:flex-row">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[var(--surface-glass)]">
                        <i class="bi {{ $iconClass }} text-lg"></i>
                    </div>
                    <div class="flex-1 space-y-3">
                        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                            <div>
                                <h3 class="text-sm font-semibold text-[var(--text-primary)] {{ !$notification->read_at ? 'font-semibold' : 'font-medium' }}">
                                    {{ $notification->data['title'] ?? 'Notifikasi' }}
                                </h3>
                                <p class="mt-1 text-sm text-[var(--text-secondary)]">
                                    {{ $notification->data['message'] ?? '' }}
                                </p>
                                @if(isset($notification->data['document_number']))
                                    <div class="mt-2 text-xs font-medium text-primary-400">
                                        <i class="bi bi-file-earmark"></i>
                                        {{ $notification->data['document_number'] }}
                                    </div>
                                @endif
                            </div>
                            <div class="text-sm text-[var(--text-tertiary)]">
                                <div>{{ $notification->created_at->diffForHumans() }}</div>
                                @if(!$notification->is_read)
                                    <x-badge type="info" size="sm" class="mt-2">Baru</x-badge>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @if(isset($notification->data['action_url']))
                                <x-button href="{{ $notification->data['action_url'] }}" size="sm" variant="secondary">
                                    <i class="bi bi-eye"></i>
                                    Lihat Detail
                                </x-button>
                            @endif
                            @if(!$notification->is_read)
                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                    @csrf
                                    <x-button type="submit" size="sm" variant="secondary">
                                        <i class="bi bi-check"></i>
                                        Tandai Dibaca
                                    </x-button>
                                </form>
                            @endif
                            <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus notifikasi ini?')">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" size="sm" variant="danger">
                                    <i class="bi bi-trash"></i>
                                </x-button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-10 text-center">
                <div class="space-y-3 text-[var(--text-tertiary)]">
                    <i class="bi bi-bell-slash text-3xl opacity-40"></i>
                    <p class="text-sm">Tidak ada notifikasi.</p>
                </div>
            </div>
        @endforelse

        @if($notifications->hasPages())
            <div class="p-4">
                {{ $notifications->withQueryString()->links() }}
            </div>
        @endif
    </x-glass-card>
</div>
@endsection
