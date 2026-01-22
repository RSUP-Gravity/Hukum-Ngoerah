<x-layouts.auth title="Login">
    <div class="glass-card-static p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-[var(--text-primary)]">Selamat Datang</h2>
            <p class="mt-2 text-[var(--text-secondary)]">Masuk ke akun Anda untuk melanjutkan</p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <x-alert type="success" class="mb-6">
                {{ session('status') }}
            </x-alert>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <!-- Username -->
            <x-input 
                type="text"
                name="username"
                label="Username"
                placeholder="Masukkan username"
                required
                autofocus
            />

            <!-- Password -->
            <div class="space-y-1.5">
                <label for="password" class="block text-sm font-medium text-[var(--text-primary)]">
                    Password
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative" x-data="{ showPassword: false }">
                    <input
                        :type="showPassword ? 'text' : 'password'"
                        name="password"
                        id="password"
                        placeholder="Masukkan password"
                        required
                        class="glass-input pr-10 {{ $errors->has('password') ? 'border-red-500' : '' }}"
                    >
                    <button 
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-[var(--text-tertiary)] hover:text-[var(--text-primary)]"
                    >
                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="remember" 
                        class="w-4 h-4 rounded border-[var(--surface-glass-border)] text-primary-500 focus:ring-primary-500"
                    >
                    <span class="text-sm text-[var(--text-secondary)]">Ingat saya</span>
                </label>
            </div>

            <!-- Submit Button -->
            <x-button type="submit" class="w-full">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                </svg>
                Masuk
            </x-button>
        </form>
    </div>

    <!-- Footer -->
    <p class="mt-8 text-center text-sm text-[var(--text-tertiary)]">
        Hubungi Bagian Hukmas jika mengalami kendala login
    </p>
</x-layouts.auth>
