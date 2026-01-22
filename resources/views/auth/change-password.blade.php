<x-layouts.auth title="Ubah Password">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-[var(--text-primary)] mb-2">Ubah Password</h1>
        <p class="text-[var(--text-secondary)]">Masukkan password baru Anda</p>
    </div>

    @if(session('warning'))
        <x-alert type="warning" class="mb-6">
            {{ session('warning') }}
        </x-alert>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
        @csrf

        <x-input
            type="password"
            name="current_password"
            label="Password Saat Ini"
            placeholder="Masukkan password saat ini"
            required
            :error="$errors->first('current_password')"
        />

        <x-input
            type="password"
            name="password"
            label="Password Baru"
            placeholder="Masukkan password baru"
            required
            :error="$errors->first('password')"
        />

        <div class="text-xs text-[var(--text-tertiary)] -mt-4">
            Password harus minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol.
        </div>

        <x-input
            type="password"
            name="password_confirmation"
            label="Konfirmasi Password Baru"
            placeholder="Ulangi password baru"
            required
        />

        <x-button type="submit" class="w-full">
            Ubah Password
        </x-button>
    </form>
</x-layouts.auth>
