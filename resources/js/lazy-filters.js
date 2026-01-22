/**
 * Lazy Filter Loader
 * 
 * Lazy loads filter options as user interacts with dropdowns
 * Improves initial page load performance
 */

class LazyFilterLoader {
    constructor(options = {}) {
        this.options = {
            baseUrl: '/documents/api/filters',
            debounceMs: 300,
            cacheTimeout: 5 * 60 * 1000, // 5 minutes
            ...options
        };
        
        this.cache = new Map();
        this.loading = new Set();
        this.debounceTimers = new Map();
        
        this.init();
    }
    
    init() {
        // Setup event listeners for lazy-loaded dropdowns
        document.querySelectorAll('[data-lazy-filter]').forEach(element => {
            this.setupDropdown(element);
        });
        
        // Load status counts on page load (lightweight)
        this.loadStatusCounts();
    }
    
    setupDropdown(element) {
        const filterType = element.getAttribute('data-lazy-filter');
        const searchInput = element.querySelector('input[type="search"], input[type="text"]');
        
        // Load options when dropdown is focused/clicked
        element.addEventListener('click', () => this.loadOptions(filterType, element), { once: true });
        element.addEventListener('focus', () => this.loadOptions(filterType, element), { once: true });
        
        // Handle search within dropdown
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.debounce(filterType, () => {
                    this.loadOptions(filterType, element, e.target.value);
                });
            });
        }
    }
    
    debounce(key, callback) {
        if (this.debounceTimers.has(key)) {
            clearTimeout(this.debounceTimers.get(key));
        }
        
        const timer = setTimeout(callback, this.options.debounceMs);
        this.debounceTimers.set(key, timer);
    }
    
    async loadOptions(filterType, element, search = '') {
        const cacheKey = `${filterType}:${search}`;
        
        // Check cache first
        if (this.isCached(cacheKey)) {
            this.renderOptions(element, this.cache.get(cacheKey).data);
            return;
        }
        
        // Prevent duplicate requests
        if (this.loading.has(cacheKey)) {
            return;
        }
        
        this.loading.add(cacheKey);
        this.showLoading(element);
        
        try {
            const url = new URL(this.options.baseUrl + '/' + filterType, window.location.origin);
            if (search) {
                url.searchParams.set('search', search);
            }
            
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.cacheResult(cacheKey, result.data);
                this.renderOptions(element, result.data);
            }
        } catch (error) {
            console.error('Failed to load filter options:', error);
            this.showError(element);
        } finally {
            this.loading.delete(cacheKey);
            this.hideLoading(element);
        }
    }
    
    async loadStatusCounts() {
        const cacheKey = 'status-counts';
        
        if (this.isCached(cacheKey)) {
            this.updateStatusBadges(this.cache.get(cacheKey).data);
            return;
        }
        
        try {
            const response = await fetch(this.options.baseUrl + '/status-counts', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            
            if (!response.ok) return;
            
            const result = await response.json();
            
            if (result.success) {
                this.cacheResult(cacheKey, result.data);
                this.updateStatusBadges(result.data);
            }
        } catch (error) {
            console.error('Failed to load status counts:', error);
        }
    }
    
    async loadAllFilters() {
        const cacheKey = 'all-filters';
        
        if (this.isCached(cacheKey)) {
            return this.cache.get(cacheKey).data;
        }
        
        try {
            const response = await fetch(this.options.baseUrl + '/all', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.cacheResult(cacheKey, result.data);
                return result.data;
            }
        } catch (error) {
            console.error('Failed to load all filters:', error);
        }
        
        return null;
    }
    
    isCached(key) {
        if (!this.cache.has(key)) return false;
        
        const cached = this.cache.get(key);
        return (Date.now() - cached.timestamp) < this.options.cacheTimeout;
    }
    
    cacheResult(key, data) {
        this.cache.set(key, {
            data,
            timestamp: Date.now()
        });
    }
    
    clearCache() {
        this.cache.clear();
    }
    
    renderOptions(element, options) {
        const optionsList = element.querySelector('[data-filter-options]');
        if (!optionsList) return;
        
        // Clear existing options (except first placeholder)
        const placeholder = optionsList.querySelector('option[value=""]');
        optionsList.innerHTML = '';
        if (placeholder) {
            optionsList.appendChild(placeholder);
        }
        
        // Add new options
        options.forEach(option => {
            const optionEl = document.createElement('option');
            optionEl.value = option.id;
            optionEl.textContent = option.name;
            if (option.code) {
                optionEl.textContent = `${option.code} - ${option.name}`;
            }
            optionsList.appendChild(optionEl);
        });
        
        // Trigger change event for any dependent components
        optionsList.dispatchEvent(new CustomEvent('optionsloaded', { detail: options }));
    }
    
    updateStatusBadges(counts) {
        Object.entries(counts).forEach(([status, count]) => {
            const badges = document.querySelectorAll(`[data-status-count="${status}"]`);
            badges.forEach(badge => {
                badge.textContent = this.formatNumber(count);
            });
        });
    }
    
    formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        }
        if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toString();
    }
    
    showLoading(element) {
        const loader = element.querySelector('[data-filter-loader]');
        if (loader) {
            loader.classList.remove('hidden');
        }
        element.classList.add('loading');
    }
    
    hideLoading(element) {
        const loader = element.querySelector('[data-filter-loader]');
        if (loader) {
            loader.classList.add('hidden');
        }
        element.classList.remove('loading');
    }
    
    showError(element) {
        const errorEl = element.querySelector('[data-filter-error]');
        if (errorEl) {
            errorEl.classList.remove('hidden');
            setTimeout(() => {
                errorEl.classList.add('hidden');
            }, 3000);
        }
    }
    
    refresh() {
        this.clearCache();
        document.querySelectorAll('[data-lazy-filter]').forEach(element => {
            const filterType = element.getAttribute('data-lazy-filter');
            this.loadOptions(filterType, element);
        });
        this.loadStatusCounts();
    }
}

// Cursor Pagination Handler
class CursorPagination {
    constructor(options = {}) {
        this.options = {
            container: null,
            loadMoreBtn: null,
            loadingIndicator: null,
            itemsPerPage: 20,
            endpoint: '',
            onItemsLoaded: null,
            ...options
        };
        
        this.cursor = null;
        this.hasMore = true;
        this.loading = false;
        
        this.init();
    }
    
    init() {
        if (this.options.loadMoreBtn) {
            this.options.loadMoreBtn.addEventListener('click', () => this.loadMore());
        }
        
        // Optional: Infinite scroll
        if (this.options.infiniteScroll && this.options.container) {
            this.setupInfiniteScroll();
        }
    }
    
    setupInfiniteScroll() {
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && this.hasMore && !this.loading) {
                this.loadMore();
            }
        }, { rootMargin: '100px' });
        
        // Observe a sentinel element at the bottom
        const sentinel = document.createElement('div');
        sentinel.className = 'pagination-sentinel';
        this.options.container.appendChild(sentinel);
        observer.observe(sentinel);
    }
    
    async loadMore() {
        if (this.loading || !this.hasMore) return;
        
        this.loading = true;
        this.showLoading();
        
        try {
            const url = new URL(this.options.endpoint, window.location.origin);
            if (this.cursor) {
                url.searchParams.set('cursor', this.cursor);
            }
            
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            // Update cursor for next request
            this.cursor = result.next_cursor;
            this.hasMore = result.next_cursor !== null;
            
            // Callback to render items
            if (this.options.onItemsLoaded) {
                this.options.onItemsLoaded(result.data);
            }
            
            // Update load more button visibility
            if (!this.hasMore && this.options.loadMoreBtn) {
                this.options.loadMoreBtn.style.display = 'none';
            }
        } catch (error) {
            console.error('Failed to load more items:', error);
        } finally {
            this.loading = false;
            this.hideLoading();
        }
    }
    
    reset() {
        this.cursor = null;
        this.hasMore = true;
        this.loading = false;
        
        if (this.options.loadMoreBtn) {
            this.options.loadMoreBtn.style.display = '';
        }
    }
    
    showLoading() {
        if (this.options.loadingIndicator) {
            this.options.loadingIndicator.classList.remove('hidden');
        }
        if (this.options.loadMoreBtn) {
            this.options.loadMoreBtn.disabled = true;
            this.options.loadMoreBtn.classList.add('loading');
        }
    }
    
    hideLoading() {
        if (this.options.loadingIndicator) {
            this.options.loadingIndicator.classList.add('hidden');
        }
        if (this.options.loadMoreBtn) {
            this.options.loadMoreBtn.disabled = false;
            this.options.loadMoreBtn.classList.remove('loading');
        }
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.lazyFilterLoader = new LazyFilterLoader();
});

// Handle Turbo/Livewire navigation
document.addEventListener('turbo:load', () => {
    if (window.lazyFilterLoader) {
        window.lazyFilterLoader.refresh();
    }
});

document.addEventListener('livewire:navigated', () => {
    if (window.lazyFilterLoader) {
        window.lazyFilterLoader.refresh();
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { LazyFilterLoader, CursorPagination };
}
