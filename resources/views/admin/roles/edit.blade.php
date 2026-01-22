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
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Edit Role</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $role->display_name }}</p>
        </div>
        <x-button href="{{ route('admin.roles.index') }}" variant="secondary">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </x-button>
    </div>

    <form action="{{ route('admin.roles.update', $role) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6">
                <x-glass-card :hover="false" class="p-6">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-shield text-primary-500"></i>
                        <h2 class="text-lg font-semibold text-[var(--text-primary)]">Informasi Role</h2>
                    </div>

                    <div class="mt-5 space-y-4">
                        <x-input name="display_name" label="Nama Tampilan" :required="true" value="{{ old('display_name', $role->display_name) }}" />

                        <div class="space-y-1.5">
                            <label for="name" class="block text-sm font-medium text-[var(--text-primary)]">Nama Sistem <span class="text-red-500" aria-hidden="true">*</span></label>
                            <input type="text" class="glass-input" id="name" name="name" value="{{ old('name', $role->name) }}"
                                   pattern="[a-z_]+" title="Hanya huruf kecil dan underscore" required
                                   {{ in_array($role->name, ['super_admin', 'admin']) ? 'readonly' : '' }}>
                            @error('name')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            @if(in_array($role->name, ['super_admin', 'admin']))
                                <p class="text-xs text-[var(--text-tertiary)]">Nama sistem role ini tidak dapat diubah.</p>
                            @else
                                <p class="text-xs text-[var(--text-tertiary)]">Huruf kecil dan underscore saja</p>
                            @endif
                        </div>

                        <x-textarea name="description" label="Deskripsi" rows="3" value="{{ old('description', $role->description) }}" />

                        <div class="space-y-1.5">
                            <label for="level" class="block text-sm font-medium text-[var(--text-primary)]">Level <span class="text-red-500" aria-hidden="true">*</span></label>
                            <input type="number" class="glass-input" id="level" name="level" value="{{ old('level', $role->level) }}" min="0" max="100" required
                                   {{ in_array($role->name, ['super_admin']) ? 'readonly' : '' }}>
                            @error('level')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-[var(--text-tertiary)]">0 = terendah, 100 = tertinggi</p>
                        </div>

                        <label class="inline-flex items-center gap-2 text-sm text-[var(--text-secondary)]">
                            <input class="h-4 w-4 rounded border-[var(--surface-glass-border)] text-primary-500 focus:ring-primary-500" type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $role->is_active) ? 'checked' : '' }}
                                   {{ in_array($role->name, ['super_admin', 'admin']) ? 'disabled' : '' }}>
                            Aktif
                        </label>
                    </div>
                </x-glass-card>

                <x-glass-card :hover="false" class="p-6">
                    <div class="space-y-2">
                        <x-button type="submit" class="w-full">
                            <i class="bi bi-check-lg"></i>
                            Simpan
                        </x-button>
                        <x-button href="{{ route('admin.roles.index') }}" variant="ghost" class="w-full">
                            Batal
                        </x-button>
                    </div>
                </x-glass-card>
            </div>

            <div class="lg:col-span-2">
                <x-glass-card :hover="false" class="p-6">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-key text-primary-500"></i>
                        <h2 class="text-lg font-semibold text-[var(--text-primary)]">Hak Akses (Permissions)</h2>
                    </div>

                    <div class="mt-5">
                        @if($role->name === 'super_admin')
                            <x-alert type="info" :dismissible="false">
                                Super Admin memiliki semua hak akses dan tidak dapat diubah.
                            </x-alert>
                        @else
                            <div class="space-y-5">
                                @foreach($permissions as $module => $modulePermissions)
                                    <div class="rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                                        <div class="flex items-center justify-between">
                                            <h6 class="text-xs font-semibold uppercase tracking-wider text-[var(--text-tertiary)]">{{ ucfirst($module) }}</h6>
                                            <label class="inline-flex items-center gap-2 text-xs text-[var(--text-tertiary)]">
                                                <input class="h-4 w-4 rounded border-[var(--surface-glass-border)] text-primary-500 focus:ring-primary-500 module-toggle" type="checkbox"
                                                       id="module_{{ $module }}" data-module="{{ $module }}">
                                                Pilih Semua
                                            </label>
                                        </div>
                                        <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                            @foreach($modulePermissions as $permission)
                                                <label class="inline-flex items-start gap-2 text-sm text-[var(--text-primary)]">
                                                    <input class="h-4 w-4 rounded border-[var(--surface-glass-border)] text-primary-500 focus:ring-primary-500 permission-checkbox permission-{{ $module }}"
                                                           type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm_{{ $permission->id }}"
                                                           {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                                    <span>{{ $permission->display_name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </x-glass-card>
            </div>
        </div>
    </form>
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
