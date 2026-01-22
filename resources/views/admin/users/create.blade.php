@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Admin'],
        ['label' => 'Pengguna', 'url' => route('admin.users.index')],
        ['label' => 'Tambah']
    ]" />
@endsection

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Tambah Pengguna</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">Buat akun pengguna baru</p>
        </div>
        <x-button href="{{ route('admin.users.index') }}" variant="secondary">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </x-button>
    </div>

    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-glass-card :hover="false" class="p-6">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-person text-primary-500"></i>
                        <h2 class="text-lg font-semibold text-[var(--text-primary)]">Informasi Pengguna</h2>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <x-input name="name" label="Nama Lengkap" :required="true" value="{{ old('name') }}" />
                            <x-input name="username" label="Username" :required="true" value="{{ old('username') }}" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <x-input type="email" name="email" label="Email" value="{{ old('email') }}" />
                            <x-input name="employee_id" label="NIP" value="{{ old('employee_id') }}" />
                        </div>

                        <x-input name="phone" label="No. Telepon" value="{{ old('phone') }}" />

                        <x-alert type="info" :dismissible="false">
                            Password akan digenerate otomatis dan ditampilkan setelah pengguna dibuat.
                        </x-alert>
                    </div>
                </x-glass-card>

                <x-glass-card :hover="false" class="p-6">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-building text-primary-500"></i>
                        <h2 class="text-lg font-semibold text-[var(--text-primary)]">Organisasi & Akses</h2>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-1.5">
                                <label for="role_id" class="block text-sm font-medium text-[var(--text-primary)]">Role <span class="text-red-500" aria-hidden="true">*</span></label>
                                <select id="role_id" name="role_id" class="glass-input" required>
                                    <option value="">Pilih Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ $role->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                    <p class="text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label for="unit_id" class="block text-sm font-medium text-[var(--text-primary)]">Unit</label>
                                <select id="unit_id" name="unit_id" class="glass-input">
                                    <option value="">Pilih Unit</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->directorate->name ?? '' }} - {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_id')
                                    <p class="text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-1.5">
                                <label for="position_id" class="block text-sm font-medium text-[var(--text-primary)]">Jabatan</label>
                                <select id="position_id" name="position_id" class="glass-input">
                                    <option value="">Pilih Jabatan</option>
                                    @foreach($positions as $position)
                                        <option value="{{ $position->id }}" {{ old('position_id') == $position->id ? 'selected' : '' }}>
                                            {{ $position->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('position_id')
                                    <p class="text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-sm font-medium text-[var(--text-primary)]">Status</label>
                                <label class="mt-2 inline-flex items-center gap-2 text-sm text-[var(--text-secondary)]">
                                    <input class="h-4 w-4 rounded border-[var(--surface-glass-border)] text-primary-500 focus:ring-primary-500" type="checkbox" id="is_active" name="is_active" value="1"
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    Aktif
                                </label>
                            </div>
                        </div>
                    </div>
                </x-glass-card>
            </div>

            <div class="space-y-4">
                <x-glass-card :hover="false" class="p-6">
                    <div class="space-y-2">
                        <x-button type="submit" class="w-full">
                            <i class="bi bi-check-lg"></i>
                            Simpan
                        </x-button>
                        <x-button href="{{ route('admin.users.index') }}" variant="ghost" class="w-full">
                            Batal
                        </x-button>
                    </div>
                </x-glass-card>
            </div>
        </div>
    </form>
</div>
@endsection
