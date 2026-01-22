@extends('layouts.app')

@section('title', 'Edit Role')

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
    <div class="row justify-content-center">
        <div class="col-lg-10">
            {{-- Page Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Edit Role</h1>
                    <p class="text-muted mb-0">{{ $role->display_name }}</p>
                </div>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>

            <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-lg-4">
                        <div class="glass-card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-shield me-2"></i>Informasi Role
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="display_name" class="form-label">Nama Tampilan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('display_name') is-invalid @enderror" 
                                           id="display_name" name="display_name" value="{{ old('display_name', $role->display_name) }}" required>
                                    @error('display_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Sistem <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $role->name) }}" 
                                           pattern="[a-z_]+" title="Hanya huruf kecil dan underscore" required
                                           {{ in_array($role->name, ['super_admin', 'admin']) ? 'readonly' : '' }}>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if(in_array($role->name, ['super_admin', 'admin']))
                                        <small class="text-muted">Nama sistem role ini tidak dapat diubah.</small>
                                    @else
                                        <small class="text-muted">Huruf kecil dan underscore saja</small>
                                    @endif
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3">{{ old('description', $role->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="level" class="form-label">Level <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('level') is-invalid @enderror" 
                                           id="level" name="level" value="{{ old('level', $role->level) }}" 
                                           min="0" max="100" required
                                           {{ in_array($role->name, ['super_admin']) ? 'readonly' : '' }}>
                                    @error('level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">0 = terendah, 100 = tertinggi</small>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                               {{ old('is_active', $role->is_active) ? 'checked' : '' }}
                                               {{ in_array($role->name, ['super_admin', 'admin']) ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="is_active">Aktif</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Simpan
                            </button>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </div>
                    
                    <div class="col-lg-8">
                        <div class="glass-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-key me-2"></i>Hak Akses (Permissions)
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($role->name === 'super_admin')
                                    <div class="alert alert-info mb-0">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Super Admin memiliki semua hak akses dan tidak dapat diubah.
                                    </div>
                                @else
                                    @foreach($permissions as $module => $modulePermissions)
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="text-uppercase text-muted mb-0">{{ ucfirst($module) }}</h6>
                                            <div class="form-check">
                                                <input class="form-check-input module-toggle" type="checkbox" 
                                                       id="module_{{ $module }}" data-module="{{ $module }}">
                                                <label class="form-check-label small" for="module_{{ $module }}">Pilih Semua</label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            @foreach($modulePermissions as $permission)
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-check">
                                                    <input class="form-check-input permission-checkbox permission-{{ $module }}" 
                                                           type="checkbox" name="permissions[]" 
                                                           value="{{ $permission->id }}" id="perm_{{ $permission->id }}"
                                                           {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                        {{ $permission->display_name }}
                                                    </label>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Module toggle
    document.querySelectorAll('.module-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const module = this.dataset.module;
            const checkboxes = document.querySelectorAll('.permission-' + module);
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
        
        // Set initial state of toggle
        const module = toggle.dataset.module;
        const checkboxes = document.querySelectorAll('.permission-' + module);
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        toggle.checked = allChecked;
    });
    
    // Update module toggle when permission changes
    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const moduleClass = Array.from(this.classList).find(c => c.startsWith('permission-') && c !== 'permission-checkbox');
            if (moduleClass) {
                const module = moduleClass.replace('permission-', '');
                const checkboxes = document.querySelectorAll('.permission-' + module);
                const toggle = document.getElementById('module_' + module);
                if (toggle) {
                    toggle.checked = Array.from(checkboxes).every(cb => cb.checked);
                }
            }
        });
    });
});
</script>
@endpush
