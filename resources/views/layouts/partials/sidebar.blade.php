<!-- Sidebar -->
<aside 
    class="glass-sidebar transform transition-all duration-300"
    :class="{
        'w-[280px]': expanded,
        'w-[80px]': !expanded,
        '-translate-x-full lg:translate-x-0': !mobileOpen,
        'translate-x-0': mobileOpen
    }"
    @mouseenter="expandOnHover()"
    @mouseleave="collapseOnLeave()"
    role="navigation"
    aria-label="Main navigation sidebar"
>
    <div class="h-full flex flex-col">
        <!-- Logo -->
        <div class="h-16 flex items-center px-4 border-b border-[var(--surface-glass-border)]">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <img 
                    src="{{ asset('images/logo.png') }}" 
                    alt="Logo RS Ngoerah" 
                    class="h-10 w-10 object-contain"
                >
                <span 
                    class="font-semibold text-lg text-gradient whitespace-nowrap overflow-hidden transition-all duration-300"
                    :class="expanded ? 'opacity-100 w-auto' : 'opacity-0 w-0'"
                >
                    Hukum RS Ngoerah
                </span>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 py-4 overflow-y-auto custom-scrollbar">
            <!-- Main Menu -->
            <div class="mb-6">
                <p 
                    class="px-6 mb-2 text-xs font-semibold uppercase tracking-wider text-[var(--text-tertiary)] transition-all duration-300"
                    :class="expanded ? 'opacity-100' : 'opacity-0'"
                >
                    Menu Utama
                </p>

                <!-- Dashboard -->
                <a 
                    href="{{ route('dashboard') }}" 
                    class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                    title="Dashboard"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span 
                        class="whitespace-nowrap overflow-hidden transition-all duration-300"
                        :class="expanded ? 'opacity-100 w-auto' : 'opacity-0 w-0'"
                    >
                        Dashboard
                    </span>
                </a>

                <!-- Dokumen -->
                <a 
                    href="{{ route('documents.index') }}" 
                    class="sidebar-item {{ request()->routeIs('documents.*') ? 'active' : '' }}"
                    title="Dokumen"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span 
                        class="whitespace-nowrap overflow-hidden transition-all duration-300"
                        :class="expanded ? 'opacity-100 w-auto' : 'opacity-0 w-0'"
                    >
                        Dokumen
                    </span>
                </a>
            </div>

            <!-- Master Data (Admin Only) -->
            @can('manage-master-data')
            <div class="mb-6">
                <p 
                    class="px-6 mb-2 text-xs font-semibold uppercase tracking-wider text-[var(--text-tertiary)] transition-all duration-300"
                    :class="expanded ? 'opacity-100' : 'opacity-0'"
                >
                    Master Data
                </p>

                <!-- Direktorat -->
                <a 
                    href="{{ route('master.directorates.index') }}" 
                    class="sidebar-item {{ request()->routeIs('master.directorates.*') ? 'active' : '' }}"
                    title="Direktorat"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span 
                        class="whitespace-nowrap overflow-hidden transition-all duration-300"
                        :class="expanded ? 'opacity-100 w-auto' : 'opacity-0 w-0'"
                    >
                        Direktorat
                    </span>
                </a>

                <!-- Unit -->
                <a 
                    href="{{ route('master.units.index') }}" 
                    class="sidebar-item {{ request()->routeIs('master.units.*') ? 'active' : '' }}"
                    title="Unit"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span 
                        class="whitespace-nowrap overflow-hidden transition-all duration-300"
                        :class="expanded ? 'opacity-100 w-auto' : 'opacity-0 w-0'"
                    >
                        Unit
                    </span>
                </a>

                <!-- Jenis Dokumen -->
                <a 
                    href="{{ route('master.document-types.index') }}" 
                    class="sidebar-item {{ request()->routeIs('master.document-types.*') ? 'active' : '' }}"
                    title="Jenis Dokumen"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    <span 
                        class="whitespace-nowrap overflow-hidden transition-all duration-300"
                        :class="expanded ? 'opacity-100 w-auto' : 'opacity-0 w-0'"
                    >
                        Jenis Dokumen
                    </span>
                </a>

                <!-- Tipe Dokumen -->
                <a 
                    href="{{ route('master.document-categories.index') }}" 
                    class="sidebar-item {{ request()->routeIs('master.document-categories.*') ? 'active' : '' }}"
                    title="Tipe Dokumen"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <span 
                        class="whitespace-nowrap overflow-hidden transition-all duration-300"
                        :class="expanded ? 'opacity-100 w-auto' : 'opacity-0 w-0'"
                    >
                        Tipe Dokumen
                    </span>
                </a>
            </div>
            @endcan

            <!-- Admin Menu -->
            @can('manage-users')
            <div class="mb-6">
                <p 
                    class="px-6 mb-2 text-xs font-semibold uppercase tracking-wider text-[var(--text-tertiary)] transition-all duration-300"
                    :class="expanded ? 'opacity-100' : 'opacity-0'"
                >
                    Administrasi
                </p>

                <!-- Users -->
                <a 
                    href="{{ route('admin.users.index') }}" 
                    class="sidebar-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                    title="Pengguna"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span 
                        class="whitespace-nowrap overflow-hidden transition-all duration-300"
                        :class="expanded ? 'opacity-100 w-auto' : 'opacity-0 w-0'"
                    >
                        Pengguna
                    </span>
                </a>

                <!-- Audit Log -->
                <a 
                    href="{{ route('admin.audit-logs.index') }}" 
                    class="sidebar-item {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}"
                    title="Audit Log"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    <span 
                        class="whitespace-nowrap overflow-hidden transition-all duration-300"
                        :class="expanded ? 'opacity-100 w-auto' : 'opacity-0 w-0'"
                    >
                        Audit Log
                    </span>
                </a>
            </div>
            @endcan
        </nav>

        <!-- Sidebar Footer -->
        <div class="border-t border-[var(--surface-glass-border)] p-4">
            <button 
                @click="toggle()"
                class="w-full flex items-center justify-center p-2 rounded-lg text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--surface-glass)] transition-all duration-200"
                title="Toggle Sidebar"
            >
                <svg 
                    class="w-5 h-5 transition-transform duration-300"
                    :class="expanded ? '' : 'rotate-180'"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
            </button>
        </div>
    </div>
</aside>
