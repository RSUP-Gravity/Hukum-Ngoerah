@extends('layouts.app')

@section('title', $user->name)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Admin'],
        ['label' => 'Pengguna', 'url' => route('admin.users.index')],
        ['label' => $user->name]
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Main Info --}}
        <div class="col-lg-4">
            <div class="glass-card mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($user->avatar)
                            <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" 
                                 class="rounded-circle" width="100" height="100" style="object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center text-white" 
                                 style="width: 100px; height: 100px; font-size: 2.5rem;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-2">{{ $user->username }}</p>
                    
                    <span class="badge bg-primary-subtle text-primary mb-3">
                        {{ $user->role->display_name ?? 'No Role' }}
                    </span>
                    
                    @if($user->is_active)
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-danger">Nonaktif</span>
                    @endif
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-flex justify-content-center gap-2">
                        @can('users.edit')
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                        @endcan
                        @can('users.reset_password')
                        <form action="{{ route('admin.users.reset-password', $user) }}" method="POST"
                              onsubmit="return confirm('Yakin ingin mereset password?')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-key me-1"></i>Reset Password
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>
            
            {{-- Contact Info --}}
            <div class="glass-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Kontak</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Email</dt>
                        <dd class="col-sm-8">{{ $user->email ?? '-' }}</dd>
                        
                        <dt class="col-sm-4 text-muted">Telepon</dt>
                        <dd class="col-sm-8">{{ $user->phone ?? '-' }}</dd>
                        
                        <dt class="col-sm-4 text-muted">NIP</dt>
                        <dd class="col-sm-8 mb-0">{{ $user->employee_id ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
            
            {{-- Organization Info --}}
            <div class="glass-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Organisasi</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Unit</dt>
                        <dd class="col-sm-8">{{ $user->unit->name ?? '-' }}</dd>
                        
                        <dt class="col-sm-4 text-muted">Direktorat</dt>
                        <dd class="col-sm-8">{{ $user->unit->directorate->name ?? '-' }}</dd>
                        
                        <dt class="col-sm-4 text-muted">Jabatan</dt>
                        <dd class="col-sm-8 mb-0">{{ $user->position->name ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        
        {{-- Permissions & Activity --}}
        <div class="col-lg-8">
            {{-- Permissions --}}
            <div class="glass-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Hak Akses</h5>
                </div>
                <div class="card-body">
                    @if($user->role && $user->role->permissions->isNotEmpty())
                        @php
                            $permissionsByModule = $user->role->permissions->groupBy('module');
                        @endphp
                        
                        <div class="row">
                            @foreach($permissionsByModule as $module => $permissions)
                            <div class="col-md-6 mb-3">
                                <h6 class="text-uppercase text-muted small mb-2">{{ ucfirst($module) }}</h6>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($permissions as $permission)
                                    <span class="badge bg-light text-dark">{{ $permission->display_name }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">Tidak ada hak akses khusus.</p>
                    @endif
                </div>
            </div>
            
            {{-- Login Activity --}}
            <div class="glass-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Aktivitas Login</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Login Terakhir</dt>
                        <dd class="col-sm-8">
                            @if($user->last_login_at)
                                {{ $user->last_login_at->format('d F Y H:i') }}
                                <small class="text-muted">({{ $user->last_login_at->diffForHumans() }})</small>
                            @else
                                <span class="text-muted">Belum pernah login</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4 text-muted">IP Terakhir</dt>
                        <dd class="col-sm-8">{{ $user->last_login_ip ?? '-' }}</dd>
                        
                        <dt class="col-sm-4 text-muted">Dibuat</dt>
                        <dd class="col-sm-8">{{ $user->created_at->format('d F Y H:i') }}</dd>
                        
                        <dt class="col-sm-4 text-muted">Diperbarui</dt>
                        <dd class="col-sm-8 mb-0">{{ $user->updated_at->format('d F Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
            
            {{-- Recent Activity Logs --}}
            <div class="glass-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Aktivitas Terbaru</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                        @forelse($recentLogs as $log)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <span class="badge bg-light text-dark me-2">{{ strtoupper($log->action) }}</span>
                                    <span>{{ $log->module }}</span>
                                    @if($log->entity_name)
                                        <span class="text-muted">- {{ $log->entity_name }}</span>
                                    @endif
                                </div>
                                <small class="text-muted">{{ $log->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            @if($log->description)
                                <small class="text-muted">{{ $log->description }}</small>
                            @endif
                        </div>
                        @empty
                        <div class="list-group-item text-center text-muted py-4">
                            Belum ada aktivitas tercatat
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
