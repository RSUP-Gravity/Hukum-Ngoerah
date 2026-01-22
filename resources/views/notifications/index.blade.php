@extends('layouts.app')

@section('title', 'Notifikasi')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Notifikasi']
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Notifikasi</h1>
            <p class="text-muted mb-0">Kelola notifikasi dan pemberitahuan</p>
        </div>
        @if($notifications->where('read_at', null)->count() > 0)
        <form action="{{ route('notifications.read-all') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-check-all me-2"></i>Tandai Semua Dibaca
            </button>
        </form>
        @endif
    </div>

    {{-- Filters --}}
    <div class="glass-card mb-4">
        <div class="card-body">
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('notifications.index') }}" 
                   class="btn {{ !request('filter') ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Semua
                </a>
                <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
                   class="btn {{ request('filter') === 'unread' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Belum Dibaca
                    @if($unreadCount > 0)
                        <span class="badge bg-danger ms-1">{{ $unreadCount }}</span>
                    @endif
                </a>
                <a href="{{ route('notifications.index', ['filter' => 'read']) }}" 
                   class="btn {{ request('filter') === 'read' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Sudah Dibaca
                </a>
            </div>
        </div>
    </div>

    {{-- Notification List --}}
    <div class="glass-card">
        <div class="card-body p-0">
            @forelse($notifications as $notification)
            <div class="notification-item p-3 border-bottom {{ !$notification->read_at ? 'bg-light' : '' }}">
                <div class="d-flex">
                    <div class="flex-shrink-0 me-3">
                        @php
                            $iconClass = match($notification->data['type'] ?? 'info') {
                                'document' => 'bi-file-earmark-text text-primary',
                                'approval' => 'bi-check-circle text-success',
                                'rejection' => 'bi-x-circle text-danger',
                                'warning' => 'bi-exclamation-triangle text-warning',
                                'expiry' => 'bi-clock text-warning',
                                default => 'bi-bell text-info'
                            };
                        @endphp
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" 
                             style="width: 48px; height: 48px;">
                            <i class="bi {{ $iconClass }} fs-5"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 {{ !$notification->read_at ? 'fw-bold' : '' }}">
                                    {{ $notification->data['title'] ?? 'Notifikasi' }}
                                </h6>
                                <p class="mb-1 text-muted">
                                    {{ $notification->data['message'] ?? '' }}
                                </p>
                                @if(isset($notification->data['document_number']))
                                    <small class="text-primary">
                                        <i class="bi bi-file-earmark me-1"></i>
                                        {{ $notification->data['document_number'] }}
                                    </small>
                                @endif
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">
                                    {{ $notification->created_at->diffForHumans() }}
                                </small>
                                @if(!$notification->read_at)
                                    <span class="badge bg-primary mt-1">Baru</span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-2">
                            @if(isset($notification->data['action_url']))
                                <a href="{{ $notification->data['action_url'] }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>Lihat Detail
                                </a>
                            @endif
                            @if(!$notification->read_at)
                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-check me-1"></i>Tandai Dibaca
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Yakin ingin menghapus notifikasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <div class="text-muted">
                    <i class="bi bi-bell-slash fs-1 d-block mb-3 opacity-25"></i>
                    <p class="mb-0">Tidak ada notifikasi.</p>
                </div>
            </div>
            @endforelse
        </div>
        @if($notifications->hasPages())
        <div class="card-footer bg-transparent">
            {{ $notifications->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

<style>
.notification-item:hover {
    background-color: var(--bs-light) !important;
}
</style>
