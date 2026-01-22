import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import './lazy-loading';
import './lazy-filters';

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Make Chart.js available globally
window.Chart = Chart;

// ============================================
// Dark Mode Toggle
// ============================================
window.darkMode = {
    init() {
        // Check localStorage or system preference
        if (localStorage.getItem('darkMode') === 'true' ||
            (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    },

    toggle() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('darkMode', isDark);
    },

    enable() {
        document.documentElement.classList.add('dark');
        localStorage.setItem('darkMode', 'true');
    },

    disable() {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('darkMode', 'false');
    },

    isDark() {
        return document.documentElement.classList.contains('dark');
    }
};

// Initialize dark mode on page load
darkMode.init();

// ============================================
// Alpine.js Components
// ============================================
document.addEventListener('alpine:init', () => {
    // Dropdown Component
    Alpine.data('dropdown', () => ({
        open: false,
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        }
    }));

    // Modal Component
    Alpine.data('modal', (initialOpen = false) => ({
        open: initialOpen,
        show() {
            this.open = true;
            document.body.style.overflow = 'hidden';
        },
        hide() {
            this.open = false;
            document.body.style.overflow = '';
        },
        toggle() {
            this.open ? this.hide() : this.show();
        }
    }));

    // Sidebar Component
    Alpine.data('sidebar', () => ({
        expanded: localStorage.getItem('sidebarExpanded') !== 'false',
        mobileOpen: false,
        toggle() {
            this.expanded = !this.expanded;
            localStorage.setItem('sidebarExpanded', this.expanded);
        },
        toggleMobile() {
            this.mobileOpen = !this.mobileOpen;
        }
    }));

    // Toast Notification Component
    Alpine.data('toast', () => ({
        notifications: [],
        add(message, type = 'info', duration = 5000) {
            const id = Date.now();
            this.notifications.push({ id, message, type });

            if (duration > 0) {
                setTimeout(() => this.remove(id), duration);
            }
        },
        remove(id) {
            this.notifications = this.notifications.filter(n => n.id !== id);
        }
    }));

    // Search Command Palette (Cmd+K)
    Alpine.data('commandPalette', () => ({
        open: false,
        query: '',
        init() {
            document.addEventListener('keydown', (e) => {
                if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                    e.preventDefault();
                    this.toggle();
                }
                if (e.key === 'Escape') {
                    this.close();
                }
            });
        },
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => {
                    this.$refs.searchInput?.focus();
                });
            }
        },
        close() {
            this.open = false;
            this.query = '';
        }
    }));
});

// ============================================
// Utility Functions
// ============================================
window.formatDate = (dateString, locale = 'id-ID') => {
    return new Date(dateString).toLocaleDateString(locale, {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

window.formatNumber = (number, locale = 'id-ID') => {
    return new Intl.NumberFormat(locale).format(number);
};

// Debounce function for search
window.debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};
