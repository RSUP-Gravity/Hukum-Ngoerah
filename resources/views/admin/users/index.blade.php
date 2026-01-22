@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Admin'],
        ['label' => 'Pengguna']
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Pengguna</h1>
            <p class="text-muted mb-0">Kelola akun pengguna sistem</p>
        </div>
        @can('users.create')
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Tambah Pengguna
        </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="glass-card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.users.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Pencarian</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" name="search" 
                                   value="{{ request('search') }}" placeholder="Cari nama, username, atau email...">
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Role</label>
                        <select name="role_id" class="form-select">
                            <option value="">Semua Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Unit</label>
                        <select name="unit_id" class="form-select">
                            <option value="">Semua Unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="active" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="glass-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Pengguna</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Unit</th>
                            <th>Jabatan</th>
                            <th>Status</th>
                            <th>Login Terakhir</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        @if($user->avatar)
                                            <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="rounded-circle" width="32" height="32">
                                        @else
                                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width: 32px; height: 32px;">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $user->name }}</div>
                                        @if($user->email)
                                            <small class="text-muted">{{ $user->email }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code>{{ $user->username }}</code>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary">{{ $user->role->display_name ?? '-' }}</span>
                            </td>
                            <td>{{ $user->unit->name ?? '-' }}</td>
                            <td>{{ $user->position->name ?? '-' }}</td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                @if($user->last_login_at)
                                    <span title="{{ $user->last_login_at->format('d/m/Y H:i') }}">
                                        {{ $user->last_login_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.users.show', $user) }}">
                                                <i class="bi bi-eye me-2"></i>Lihat
                                            </a>
                                        </li>
                                        @can('users.edit')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.users.edit', $user) }}">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a>
                                        </li>
                                        @endcan
                                        @can('users.reset_password')
                                        <li>
                                            <form action="{{ route('admin.users.reset-password', $user) }}" method="POST"
                                                  onsubmit="return confirm('Yakin ingin mereset password pengguna ini?')">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bi bi-key me-2"></i>Reset Password
                                                </button>
                                            </form>
                                        </li>
                                        @endcan
                                        @can('users.edit')
                                        @if($user->id !== auth()->id())
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    @if($user->is_active)
                                                        <i class="bi bi-person-x me-2"></i>Nonaktifkan
                                                    @else
                                                        <i class="bi bi-person-check me-2"></i>Aktifkan
                                                    @endif
                                                </button>
                                            </form>
                                        </li>
                                        @endif
                                        @endcan
                                        @can('users.delete')
                                        @if($user->id !== auth()->id())
                                        <li>
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                                  onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>Hapus
                                                </button>
                                            </form>
                                        </li>
                                        @endif
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-3 opacity-25"></i>
                                    <p class="mb-2">Tidak ada pengguna ditemukan.</p>
                                    @can('users.create')
                                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i>Tambah Pengguna
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($users->hasPages())
        <div class="card-footer bg-transparent">
            {{ $users->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
