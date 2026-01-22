@extends('layouts.app')

@section('title', 'Profil Saya')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Profil']
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Profile Info --}}
        <div class="col-lg-4 mb-4">
            <div class="glass-card">
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block mb-3">
                        @if($user->avatar)
                            <img src="{{ Storage::url($user->avatar) }}" 
                                 class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center" 
                                 style="width: 120px; height: 120px;">
                                <span class="text-white fs-1 fw-bold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                            </div>
                        @endif
                    </div>
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-2">{{ $user->nip }}</p>
                    <span class="badge bg-primary-soft text-primary">{{ $user->role->display_name ?? $user->role->name }}</span>
                </div>
                <hr class="my-0">
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="py-2 d-flex align-items-center border-bottom">
                            <i class="bi bi-envelope text-primary me-3"></i>
                            <span>{{ $user->email ?? '-' }}</span>
                        </li>
                        <li class="py-2 d-flex align-items-center border-bottom">
                            <i class="bi bi-telephone text-primary me-3"></i>
                            <span>{{ $user->phone ?? '-' }}</span>
                        </li>
                        <li class="py-2 d-flex align-items-center border-bottom">
                            <i class="bi bi-building text-primary me-3"></i>
                            <span>{{ $user->unit->directorate->name ?? '-' }}</span>
                        </li>
                        <li class="py-2 d-flex align-items-center border-bottom">
                            <i class="bi bi-diagram-3 text-primary me-3"></i>
                            <span>{{ $user->unit->name ?? '-' }}</span>
                        </li>
                        <li class="py-2 d-flex align-items-center">
                            <i class="bi bi-person-badge text-primary me-3"></i>
                            <span>{{ $user->position->name ?? '-' }}</span>
                        </li>
                    </ul>
                </div>
                <div class="card-footer bg-transparent text-center">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i> Edit Profil
                    </a>
                </div>
            </div>
        </div>

        {{-- Details --}}
        <div class="col-lg-8">
            {{-- Login Activity --}}
            <div class="glass-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Aktivitas Login</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="p-3 rounded bg-light-subtle border">
                                <p class="text-muted mb-1 small">Login Terakhir</p>
                                <h6 class="mb-0">{{ $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : '-' }}</h6>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded bg-light-subtle border">
                                <p class="text-muted mb-1 small">IP Address</p>
                                <h6 class="mb-0"><code>{{ $user->last_login_ip ?? '-' }}</code></h6>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded bg-light-subtle border">
                                <p class="text-muted mb-1 small">Bergabung</p>
                                <h6 class="mb-0">{{ $user->created_at->format('d M Y') }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Permissions --}}
            <div class="glass-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-shield-check me-2"></i>Hak Akses</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @if($user->role && $user->role->permissions)
                            @foreach($user->role->permissions as $permission)
                                <span class="badge bg-secondary-soft text-secondary">
                                    {{ $permission->display_name ?? $permission->name }}
                                </span>
                            @endforeach
                        @else
                            <span class="text-muted">Tidak ada hak akses khusus.</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="glass-card">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Aksi Cepat</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-pencil me-2"></i>Edit Profil
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('password.change') }}" class="btn btn-outline-warning w-100">
                                <i class="bi bi-key me-2"></i>Ganti Password
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('documents.index') }}?created_by={{ $user->id }}" class="btn btn-outline-info w-100">
                                <i class="bi bi-file-earmark-text me-2"></i>Dokumen Saya
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-bell me-2"></i>Notifikasi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
