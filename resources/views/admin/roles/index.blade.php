@extends('layouts.app')

@section('title', 'Manajemen Role')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Admin'],
        ['label' => 'Role']
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Role</h1>
            <p class="text-muted mb-0">Kelola role dan hak akses pengguna</p>
        </div>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Tambah Role
        </a>
    </div>

    {{-- Filters --}}
    <div class="glass-card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.roles.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Pencarian</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" name="search" 
                                   value="{{ request('search') }}" placeholder="Cari nama role...">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="active" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Roles Table --}}
    <div class="glass-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Nama Sistem</th>
                            <th>Level</th>
                            <th>Jumlah Pengguna</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $role->display_name }}</div>
                                @if($role->description)
                                    <small class="text-muted">{{ Str::limit($role->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <code>{{ $role->name }}</code>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">Level {{ $role->level }}</span>
                            </td>
                            <td>
                                {{ $role->users_count }} pengguna
                            </td>
                            <td>
                                @if($role->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.roles.show', $role) }}">
                                                <i class="bi bi-eye me-2"></i>Lihat
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.roles.edit', $role) }}">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a>
                                        </li>
                                        @if(!in_array($role->name, ['super_admin', 'admin']))
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                                  onsubmit="return confirm('Yakin ingin menghapus role ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger"
                                                        {{ $role->users_count > 0 ? 'disabled' : '' }}>
                                                    <i class="bi bi-trash me-2"></i>Hapus
                                                </button>
                                            </form>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-shield fs-1 d-block mb-3 opacity-25"></i>
                                    <p class="mb-0">Tidak ada role ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($roles->hasPages())
        <div class="card-footer bg-transparent">
            {{ $roles->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
