@extends('layouts.app')

@section('title', 'Profil Saya')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Profil']
    ]" />
@endsection

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Profile Info --}}
        <div class="lg:col-span-4 space-y-6">
            <x-glass-card :hover="false" class="p-6">
                <div class="text-center">
                    <div class="relative mx-auto mb-4 h-28 w-28">
                        @if(Auth::user()->avatar)
                            <img src="{{ Storage::url(Auth::user()->avatar) }}"
                                 class="h-28 w-28 rounded-full object-cover">
                        @else
                            <div class="flex h-28 w-28 items-center justify-center rounded-full bg-primary-500/20 text-3xl font-semibold text-primary-300">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <x-button type="button" size="sm" class="!p-2 absolute bottom-0 right-0" @click="$dispatch('open-modal', 'avatarModal')">
                            <i class="bi bi-camera"></i>
                        </x-button>
                    </div>
                    <h2 class="text-lg font-semibold text-[var(--text-primary)]">{{ Auth::user()->name }}</h2>
                    <p class="text-sm text-[var(--text-tertiary)]">{{ Auth::user()->nip }}</p>
                    <div class="mt-3">
                        <x-badge type="info" size="sm">{{ Auth::user()->role->display_name ?? Auth::user()->role->name }}</x-badge>
                    </div>
                </div>
                <div class="mt-6 space-y-3 border-t border-[var(--surface-glass-border)] pt-4">
                    <div class="flex items-center gap-3 text-sm text-[var(--text-secondary)]">
                        <i class="bi bi-envelope text-primary-400"></i>
                        <span>{{ Auth::user()->email ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-[var(--text-secondary)]">
                        <i class="bi bi-telephone text-primary-400"></i>
                        <span>{{ Auth::user()->phone ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-[var(--text-secondary)]">
                        <i class="bi bi-building text-primary-400"></i>
                        <span>{{ Auth::user()->unit->name ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-[var(--text-secondary)]">
                        <i class="bi bi-person-badge text-primary-400"></i>
                        <span>{{ Auth::user()->position->name ?? '-' }}</span>
                    </div>
                </div>
            </x-glass-card>

            {{-- Login Activity --}}
            <x-glass-card :hover="false" class="p-6">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]">Aktivitas Login Terakhir</h3>
                <div class="mt-4 space-y-3 text-sm text-[var(--text-secondary)]">
                    <div class="flex items-center justify-between border-b border-[var(--surface-glass-border)] pb-3">
                        <span>Login Terakhir</span>
                        <span class="text-[var(--text-primary)]">{{ Auth::user()->last_login_at ? Auth::user()->last_login_at->format('d M Y H:i') : '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between border-b border-[var(--surface-glass-border)] pb-3">
                        <span>IP Address</span>
                        <span class="text-xs font-mono text-[var(--text-tertiary)]">{{ Auth::user()->last_login_ip ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Bergabung</span>
                        <span class="text-[var(--text-primary)]">{{ Auth::user()->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </x-glass-card>
        </div>

        {{-- Edit Forms --}}
        <div class="lg:col-span-8 space-y-6">
            {{-- Profile Form --}}
            <x-glass-card :hover="false" class="p-6">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]">Edit Informasi Profil</h3>
                <form action="{{ route('profile.update') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-[var(--text-primary)]">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" class="glass-input @error('name') border-red-500 @enderror"
                                   name="name" value="{{ old('name', Auth::user()->name) }}" required>
                            @error('name')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-[var(--text-primary)]">NIP</label>
                            <input type="text" class="glass-input" value="{{ Auth::user()->nip }}" readonly>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-[var(--text-primary)]">Email</label>
                            <input type="email" class="glass-input @error('email') border-red-500 @enderror"
                                   name="email" value="{{ old('email', Auth::user()->email) }}">
                            @error('email')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-[var(--text-primary)]">Telepon</label>
                            <input type="text" class="glass-input @error('phone') border-red-500 @enderror"
                                   name="phone" value="{{ old('phone', Auth::user()->phone) }}">
                            @error('phone')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <x-button type="submit">
                        <i class="bi bi-check-lg"></i>
                        Simpan Perubahan
                    </x-button>
                </form>
            </x-glass-card>

            {{-- Password Form --}}
            <x-glass-card :hover="false" class="p-6">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]">Ubah Password</h3>
                <form action="{{ route('profile.password') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="text-sm font-medium text-[var(--text-primary)]">Password Saat Ini <span class="text-red-500">*</span></label>
                            <input type="password" class="glass-input @error('current_password') border-red-500 @enderror"
                                   name="current_password" required>
                            @error('current_password')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-[var(--text-primary)]">Password Baru <span class="text-red-500">*</span></label>
                            <input type="password" class="glass-input @error('password') border-red-500 @enderror"
                                   name="password" required>
                            @error('password')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-[var(--text-tertiary)]">Minimal 8 karakter</p>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-[var(--text-primary)]">Konfirmasi Password <span class="text-red-500">*</span></label>
                            <input type="password" class="glass-input" name="password_confirmation" required>
                        </div>
                    </div>
                    <x-button type="submit" variant="secondary">
                        <i class="bi bi-key"></i>
                        Ubah Password
                    </x-button>
                </form>
            </x-glass-card>
        </div>
    </div>
</div>

{{-- Avatar Modal --}}
<x-modal name="avatarModal" maxWidth="lg">
    <x-slot name="header">
        <h3 class="text-lg font-semibold text-[var(--text-primary)]">Ubah Foto Profil</h3>
    </x-slot>

    <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')
        <div class="text-center">
            <img id="avatarPreview" src="{{ Auth::user()->avatar ? Storage::url(Auth::user()->avatar) : '' }}"
                 class="mx-auto h-36 w-36 rounded-full object-cover {{ Auth::user()->avatar ? '' : 'hidden' }}">
            <div id="avatarPlaceholder" class="mx-auto flex h-36 w-36 items-center justify-center rounded-full bg-[var(--surface-glass)] {{ Auth::user()->avatar ? 'hidden' : '' }}">
                <i class="bi bi-camera text-3xl text-[var(--text-tertiary)]"></i>
            </div>
        </div>
        <div class="space-y-2">
            <input type="file" class="glass-input file:mr-4 file:rounded-lg file:border-0 file:bg-[var(--surface-glass)] file:px-3 file:py-2 file:text-sm file:text-[var(--text-primary)]" name="avatar" accept="image/*"
                   onchange="previewAvatar(this)">
            <p class="text-xs text-[var(--text-tertiary)]">JPG, PNG maksimal 2MB</p>
        </div>
        <div class="flex flex-col-reverse gap-2 border-t border-[var(--surface-glass-border)] pt-4 sm:flex-row sm:justify-end">
            <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'avatarModal')">Batal</x-button>
            <x-button type="submit">Simpan</x-button>
        </div>
    </form>
</x-modal>
@endsection

@push('scripts')
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
            document.getElementById('avatarPreview').classList.remove('hidden');
            document.getElementById('avatarPlaceholder').classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
