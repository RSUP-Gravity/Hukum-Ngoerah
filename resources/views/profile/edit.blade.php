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
                        @if(Auth::user()->avatar)
                            <img src="{{ Storage::url(Auth::user()->avatar) }}" 
                                 class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center" 
                                 style="width: 120px; height: 120px;">
                                <span class="text-white fs-1 fw-bold">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </span>
                            </div>
                        @endif
                        <button type="button" class="btn btn-sm btn-primary rounded-circle position-absolute" 
                                style="bottom: 0; right: 0;" data-bs-toggle="modal" data-bs-target="#avatarModal">
                            <i class="bi bi-camera"></i>
                        </button>
                    </div>
                    <h5 class="mb-1">{{ Auth::user()->name }}</h5>
                    <p class="text-muted mb-2">{{ Auth::user()->nip }}</p>
                    <span class="badge bg-primary-soft text-primary">{{ Auth::user()->role->display_name ?? Auth::user()->role->name }}</span>
                </div>
                <hr class="my-0">
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="py-2 d-flex align-items-center border-bottom">
                            <i class="bi bi-envelope text-primary me-3"></i>
                            <span>{{ Auth::user()->email ?? '-' }}</span>
                        </li>
                        <li class="py-2 d-flex align-items-center border-bottom">
                            <i class="bi bi-telephone text-primary me-3"></i>
                            <span>{{ Auth::user()->phone ?? '-' }}</span>
                        </li>
                        <li class="py-2 d-flex align-items-center border-bottom">
                            <i class="bi bi-building text-primary me-3"></i>
                            <span>{{ Auth::user()->unit->name ?? '-' }}</span>
                        </li>
                        <li class="py-2 d-flex align-items-center">
                            <i class="bi bi-person-badge text-primary me-3"></i>
                            <span>{{ Auth::user()->position->name ?? '-' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Login Activity --}}
            <div class="glass-card mt-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0">Aktivitas Login Terakhir</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="py-2 d-flex justify-content-between border-bottom">
                            <span class="text-muted">Login Terakhir</span>
                            <span>{{ Auth::user()->last_login_at ? Auth::user()->last_login_at->format('d M Y H:i') : '-' }}</span>
                        </li>
                        <li class="py-2 d-flex justify-content-between border-bottom">
                            <span class="text-muted">IP Address</span>
                            <span><code>{{ Auth::user()->last_login_ip ?? '-' }}</code></span>
                        </li>
                        <li class="py-2 d-flex justify-content-between">
                            <span class="text-muted">Bergabung</span>
                            <span>{{ Auth::user()->created_at->format('d M Y') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Edit Forms --}}
        <div class="col-lg-8">
            {{-- Profile Form --}}
            <div class="glass-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0">Edit Informasi Profil</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name', Auth::user()->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NIP</label>
                                <input type="text" class="form-control" value="{{ Auth::user()->nip }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email', Auth::user()->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Telepon</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       name="phone" value="{{ old('phone', Auth::user()->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Password Form --}}
            <div class="glass-card">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0">Ubah Password</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                       name="current_password" required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Minimal 8 karakter</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" 
                                       name="password_confirmation" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-key me-2"></i>Ubah Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Avatar Modal --}}
<div class="modal fade" id="avatarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Foto Profil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img id="avatarPreview" src="{{ Auth::user()->avatar ? Storage::url(Auth::user()->avatar) : '' }}" 
                             class="rounded-circle d-none" style="width: 150px; height: 150px; object-fit: cover;">
                        <div id="avatarPlaceholder" class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center {{ Auth::user()->avatar ? 'd-none' : '' }}" 
                             style="width: 150px; height: 150px;">
                            <i class="bi bi-camera fs-1 text-muted"></i>
                        </div>
                    </div>
                    <input type="file" class="form-control" name="avatar" accept="image/*" 
                           onchange="previewAvatar(this)">
                    <small class="text-muted">JPG, PNG maksimal 2MB</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
            document.getElementById('avatarPreview').classList.remove('d-none');
            document.getElementById('avatarPlaceholder').classList.add('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
