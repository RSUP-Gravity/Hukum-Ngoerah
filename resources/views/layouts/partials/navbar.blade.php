<!-- Navbar -->
<header 
    class="fixed top-0 right-0 z-20 h-16 transition-all duration-300 bg-[var(--surface-glass)] backdrop-blur-xl border-b border-[var(--surface-glass-border)]"
    :class="expanded ? 'lg:left-[280px]' : 'lg:left-[80px]'"
    style="left: 0;"
    role="banner"
    aria-label="Top navigation bar"
>
    <div class="h-full flex items-center justify-between px-4 lg:px-6">
        <!-- Left Section -->
        <div class="flex items-center gap-4">
            <!-- Mobile Menu Button -->
            <button 
                @click="toggleMobile()"
                class="lg:hidden p-2 rounded-lg text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--surface-glass)] transition-colors"
                aria-label="Toggle mobile menu"
                aria-expanded="mobileOpen"
                aria-controls="mobile-sidebar"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Search Bar -->
            <div class="hidden sm:block relative">
                <button 
                    @click="$dispatch('open-command-palette')"
                    class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm text-[var(--text-tertiary)] bg-[var(--surface-glass)] border border-[var(--surface-glass-border)] hover:border-[var(--color-primary)] transition-colors w-64"
                    aria-label="Open search dialog (Ctrl+K or Cmd+K)"
                    aria-keyshortcuts="Control+K Meta+K"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span>Cari dokumen...</span>
                    <kbd class="ml-auto px-2 py-0.5 text-xs font-medium rounded bg-[var(--surface-glass-border)]" aria-hidden="true">⌘K</kbd>
                </button>
            </div>
        </div>

        <!-- Right Section -->
        <div class="flex items-center gap-2">
            <!-- Dark Mode Toggle -->
            <button 
                @click="darkMode.toggle()"
                class="p-2 rounded-lg text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--surface-glass)] transition-colors"
                title="Toggle Dark Mode"
                aria-label="Toggle dark mode"
                aria-pressed="localStorage.getItem('darkMode') === 'true'"
            >
                <!-- Sun Icon (Light Mode) -->
                <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <!-- Moon Icon (Dark Mode) -->
                <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                </svg>
            </button>

            <!-- Notifications -->
            <x-dropdown align="right" width="72">
                <x-slot name="trigger">
                    <button 
                        class="relative p-2 rounded-lg text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--surface-glass)] transition-colors"
                        aria-label="Notifications {{ isset($unreadNotifications) && $unreadNotifications > 0 ? '(' . $unreadNotifications . ' unread)' : '' }}"
                        aria-haspopup="true"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <!-- Notification Badge -->
                        @if(isset($unreadNotifications) && $unreadNotifications > 0)
                            <span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white" aria-hidden="true">
                                {{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}
                            </span>
                        @endif
                    </button>
                </x-slot>

                <div class="py-2" role="menu" aria-orientation="vertical">
                    <div class="px-4 py-2 border-b border-[var(--surface-glass-border)]">
                        <h3 class="font-semibold text-sm" id="notifications-heading">Notifikasi</h3>
                    </div>
                    
                    <div class="max-h-80 overflow-y-auto custom-scrollbar" role="list" aria-labelledby="notifications-heading">
                        @forelse($notifications ?? [] as $notification)
                            <a 
                                href="{{ $notification->link ?? '#' }}"
                                class="dropdown-item py-3"
                                role="listitem"
                            >
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate">{{ $notification->title }}</p>
                                    <p class="text-xs text-[var(--text-tertiary)]">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                            </a>
                        @empty
                            <div class="px-4 py-8 text-center text-sm text-[var(--text-tertiary)]">
                                Tidak ada notifikasi
                            </div>
                        @endforelse
                    </div>

                    @if(isset($notifications) && count($notifications) > 0)
                        <div class="px-4 py-2 border-t border-[var(--surface-glass-border)]">
                            <a href="{{ route('notifications.index') }}" class="text-sm text-primary-500 hover:text-primary-600">
                                Lihat semua notifikasi
                            </a>
                        </div>
                    @endif
                </div>
            </x-dropdown>

            <!-- User Menu -->
            <x-dropdown align="right" width="56">
                <x-slot name="trigger">
                    <button class="flex items-center gap-3 p-1.5 rounded-lg hover:bg-[var(--surface-glass)] transition-colors">
                        <div class="w-8 h-8 rounded-full bg-gradient-primary flex items-center justify-center text-white text-sm font-medium">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="hidden lg:block text-left">
                            <p class="text-sm font-medium text-[var(--text-primary)]">
                                {{ auth()->user()->name ?? 'User' }}
                            </p>
                            <p class="text-xs text-[var(--text-tertiary)]">
                                {{ auth()->user()->role->name ?? 'Role' }}
                            </p>
                        </div>
                        <svg class="w-4 h-4 text-[var(--text-tertiary)] hidden lg:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </x-slot>

                <x-dropdown-item href="{{ route('profile.edit') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Profil Saya
                </x-dropdown-item>

                <div class="border-t border-[var(--surface-glass-border)] my-1"></div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-item 
                        type="submit"
                        class="text-red-600 hover:text-red-700"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Keluar
                    </x-dropdown-item>
                </form>
            </x-dropdown>
        </div>
    </div>
</header>
