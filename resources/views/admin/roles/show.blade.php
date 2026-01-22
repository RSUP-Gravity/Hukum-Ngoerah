@extends('layouts.app')

@section('title', $role->display_name)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Admin'],
        ['label' => 'Role', 'url' => route('admin.roles.index')],
        ['label' => $role->display_name]
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-4">
            {{-- Role Info --}}
            <div class="glass-card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="mb-1">{{ $role->display_name }}</h4>
                            <code>{{ $role->name }}</code>
                        </div>
                        @if($role->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-danger">Nonaktif</span>
                        @endif
                    </div>
                    
                    @if($role->description)
                        <p class="text-muted mb-0">{{ $role->description }}</p>
                    @endif
                </div>
                <div class="card-footer bg-transparent d-flex justify-content-between">
                    <span class="text-muted">Level {{ $role->level }}</span>
                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                </div>
            </div>
            
            {{-- Users with this Role --}}
            <div class="glass-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Pengguna</h5>
                    <span class="badge bg-primary">{{ $role->users->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                        @forelse($role->users as $user)
                        <a href="{{ route('admin.users.show', $user) }}" class="list-group-item list-group-item-action d-flex align-items-center">
                            <div class="me-2">
                                @if($user->avatar)
                                    <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" 
                                         class="rounded-circle" width="32" height="32">
                                @else
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" 
                                         style="width: 32px; height: 32px;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div class="fw-medium">{{ $user->name }}</div>
                                <small class="text-muted">{{ $user->username }}</small>
                            </div>
                        </a>
                        @empty
                        <div class="list-group-item text-center text-muted py-4">
                            Tidak ada pengguna dengan role ini
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            {{-- Permissions --}}
            <div class="glass-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Hak Akses</h5>
                </div>
                <div class="card-body">
                    @if($role->name === 'super_admin')
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-shield-check me-2"></i>
                            Super Admin memiliki akses penuh ke seluruh sistem.
                        </div>
                    @elseif($permissionsByModule->isEmpty())
                        <p class="text-muted mb-0">Tidak ada hak akses yang ditentukan.</p>
                    @else
                        <div class="row">
                            @foreach($permissionsByModule as $module => $permissions)
                            <div class="col-md-6 mb-4">
                                <h6 class="text-uppercase text-muted mb-2">{{ ucfirst($module) }}</h6>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($permissions as $permission)
                                    <span class="badge bg-primary-subtle text-primary">{{ $permission->display_name }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
