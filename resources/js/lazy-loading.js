/**
 * Lazy Loading Module for Hukum Ngoerah
 * 
 * Implements intersection observer-based lazy loading for:
 * - Images
 * - Iframes
 * - Heavy components
 * - Data tables
 */

class LazyLoader {
    constructor(options = {}) {
        this.options = {
            root: null,
            rootMargin: '50px 0px',
            threshold: 0.01,
            ...options
        };
        
        this.observer = null;
        this.loadedElements = new Set();
        
        this.init();
    }
    
    init() {
        // Check for Intersection Observer support
        if ('IntersectionObserver' in window) {
            this.observer = new IntersectionObserver(
                this.handleIntersection.bind(this),
                this.options
            );
            
            this.observeElements();
        } else {
            // Fallback: load all elements immediately
            this.loadAllElements();
        }
        
        // Re-observe when new content is added
        this.setupMutationObserver();
    }
    
    handleIntersection(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                this.loadElement(entry.target);
                this.observer.unobserve(entry.target);
            }
        });
    }
    
    observeElements() {
        // Lazy images
        document.querySelectorAll('[data-lazy-src]').forEach(el => {
            if (!this.loadedElements.has(el)) {
                this.observer.observe(el);
            }
        });
        
        // Lazy iframes
        document.querySelectorAll('[data-lazy-iframe]').forEach(el => {
            if (!this.loadedElements.has(el)) {
                this.observer.observe(el);
            }
        });
        
        // Lazy components
        document.querySelectorAll('[data-lazy-component]').forEach(el => {
            if (!this.loadedElements.has(el)) {
                this.observer.observe(el);
            }
        });
        
        // Lazy tables
        document.querySelectorAll('[data-lazy-table]').forEach(el => {
            if (!this.loadedElements.has(el)) {
                this.observer.observe(el);
            }
        });
    }
    
    loadElement(element) {
        if (this.loadedElements.has(element)) return;
        
        // Add loading class
        element.classList.add('lazy-loading');
        
        if (element.hasAttribute('data-lazy-src')) {
            this.loadImage(element);
        } else if (element.hasAttribute('data-lazy-iframe')) {
            this.loadIframe(element);
        } else if (element.hasAttribute('data-lazy-component')) {
            this.loadComponent(element);
        } else if (element.hasAttribute('data-lazy-table')) {
            this.loadTable(element);
        }
        
        this.loadedElements.add(element);
    }
    
    loadImage(element) {
        const src = element.getAttribute('data-lazy-src');
        const srcset = element.getAttribute('data-lazy-srcset');
        
        // Create a new image to preload
        const img = new Image();
        
        img.onload = () => {
            if (element.tagName === 'IMG') {
                element.src = src;
                if (srcset) element.srcset = srcset;
            } else {
                element.style.backgroundImage = `url(${src})`;
            }
            
            element.classList.remove('lazy-loading');
            element.classList.add('lazy-loaded');
            element.removeAttribute('data-lazy-src');
            element.removeAttribute('data-lazy-srcset');
            
            // Dispatch custom event
            element.dispatchEvent(new CustomEvent('lazyloaded'));
        };
        
        img.onerror = () => {
            element.classList.remove('lazy-loading');
            element.classList.add('lazy-error');
            console.warn('Failed to load image:', src);
        };
        
        img.src = src;
        if (srcset) img.srcset = srcset;
    }
    
    loadIframe(element) {
        const src = element.getAttribute('data-lazy-iframe');
        const iframe = document.createElement('iframe');
        
        // Copy attributes
        Array.from(element.attributes).forEach(attr => {
            if (attr.name !== 'data-lazy-iframe' && attr.name !== 'class') {
                iframe.setAttribute(attr.name, attr.value);
            }
        });
        
        iframe.src = src;
        iframe.className = element.className;
        iframe.classList.remove('lazy-loading');
        iframe.classList.add('lazy-loaded');
        
        iframe.onload = () => {
            element.replaceWith(iframe);
        };
    }
    
    loadComponent(element) {
        const componentUrl = element.getAttribute('data-lazy-component');
        const method = element.getAttribute('data-method') || 'GET';
        
        fetch(componentUrl, {
            method: method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => response.text())
        .then(html => {
            element.innerHTML = html;
            element.classList.remove('lazy-loading');
            element.classList.add('lazy-loaded');
            element.removeAttribute('data-lazy-component');
            
            // Initialize any Alpine.js components
            if (window.Alpine) {
                window.Alpine.initTree(element);
            }
            
            element.dispatchEvent(new CustomEvent('componentloaded'));
        })
        .catch(error => {
            element.classList.remove('lazy-loading');
            element.classList.add('lazy-error');
            console.error('Failed to load component:', error);
        });
    }
    
    loadTable(element) {
        const tableUrl = element.getAttribute('data-lazy-table');
        const containerId = element.getAttribute('data-container') || element.id;
        
        // Show loading spinner
        element.innerHTML = `
            <div class="flex items-center justify-center py-8">
                <svg class="animate-spin h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-2 text-secondary">Memuat data...</span>
            </div>
        `;
        
        fetch(tableUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => response.text())
        .then(html => {
            element.innerHTML = html;
            element.classList.remove('lazy-loading');
            element.classList.add('lazy-loaded');
            element.removeAttribute('data-lazy-table');
            
            element.dispatchEvent(new CustomEvent('tableloaded'));
        })
        .catch(error => {
            element.innerHTML = `
                <div class="text-center py-8 text-red-500">
                    <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p>Gagal memuat data</p>
                </div>
            `;
            console.error('Failed to load table:', error);
        });
    }
    
    loadAllElements() {
        document.querySelectorAll('[data-lazy-src], [data-lazy-iframe], [data-lazy-component], [data-lazy-table]')
            .forEach(el => this.loadElement(el));
    }
    
    setupMutationObserver() {
        const mutationObserver = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1) {
                        // Check if the added node is a lazy element
                        if (node.hasAttribute && (
                            node.hasAttribute('data-lazy-src') ||
                            node.hasAttribute('data-lazy-iframe') ||
                            node.hasAttribute('data-lazy-component') ||
                            node.hasAttribute('data-lazy-table')
                        )) {
                            this.observer.observe(node);
                        }
                        
                        // Check for lazy elements within the added node
                        if (node.querySelectorAll) {
                            node.querySelectorAll('[data-lazy-src], [data-lazy-iframe], [data-lazy-component], [data-lazy-table]')
                                .forEach(el => this.observer.observe(el));
                        }
                    }
                });
            });
        });
        
        mutationObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    // Manual trigger for elements
    load(selector) {
        const elements = document.querySelectorAll(selector);
        elements.forEach(el => this.loadElement(el));
    }
    
    // Refresh observer for dynamically added elements
    refresh() {
        this.observeElements();
    }
}

// Native lazy loading enhancement
class NativeLazyLoad {
    constructor() {
        this.init();
    }
    
    init() {
        // Add loading="lazy" to images and iframes without it
        if ('loading' in HTMLImageElement.prototype) {
            document.querySelectorAll('img:not([loading])').forEach(img => {
                // Only add lazy loading if image is below the fold
                const rect = img.getBoundingClientRect();
                if (rect.top > window.innerHeight) {
                    img.loading = 'lazy';
                }
            });
            
            document.querySelectorAll('iframe:not([loading])').forEach(iframe => {
                iframe.loading = 'lazy';
            });
        }
    }
}

// Lazy Load Charts
class LazyChartLoader {
    constructor(options = {}) {
        this.options = {
            rootMargin: '100px 0px',
            threshold: 0.1,
            ...options
        };
        
        this.charts = new Map();
        this.observer = null;
        
        this.init();
    }
    
    init() {
        if ('IntersectionObserver' in window) {
            this.observer = new IntersectionObserver(
                this.handleIntersection.bind(this),
                this.options
            );
            
            this.observeCharts();
        } else {
            this.loadAllCharts();
        }
    }
    
    handleIntersection(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                this.loadChart(entry.target);
                this.observer.unobserve(entry.target);
            }
        });
    }
    
    observeCharts() {
        document.querySelectorAll('[data-lazy-chart]').forEach(el => {
            this.observer.observe(el);
        });
    }
    
    loadChart(element) {
        const chartType = element.getAttribute('data-chart-type') || 'bar';
        const chartData = element.getAttribute('data-chart-data');
        const chartOptions = element.getAttribute('data-chart-options');
        
        // Show loading indicator
        element.innerHTML = `
            <div class="flex items-center justify-center h-full">
                <div class="animate-pulse text-gray-400">Loading chart...</div>
            </div>
        `;
        
        // Wait for Chart.js to be available
        this.waitForChartJS().then(() => {
            const canvas = document.createElement('canvas');
            element.innerHTML = '';
            element.appendChild(canvas);
            
            try {
                const data = JSON.parse(chartData);
                const options = chartOptions ? JSON.parse(chartOptions) : {};
                
                const chart = new Chart(canvas.getContext('2d'), {
                    type: chartType,
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        ...options
                    }
                });
                
                this.charts.set(element.id || element, chart);
                element.classList.add('chart-loaded');
                element.dispatchEvent(new CustomEvent('chartloaded', { detail: chart }));
            } catch (e) {
                console.error('Failed to initialize chart:', e);
                element.innerHTML = '<div class="text-red-500 text-center">Gagal memuat chart</div>';
            }
        });
    }
    
    waitForChartJS() {
        return new Promise(resolve => {
            if (window.Chart) {
                resolve();
            } else {
                const check = setInterval(() => {
                    if (window.Chart) {
                        clearInterval(check);
                        resolve();
                    }
                }, 100);
                
                // Timeout after 10 seconds
                setTimeout(() => {
                    clearInterval(check);
                    resolve();
                }, 10000);
            }
        });
    }
    
    loadAllCharts() {
        document.querySelectorAll('[data-lazy-chart]').forEach(el => {
            this.loadChart(el);
        });
    }
    
    getChart(id) {
        return this.charts.get(id);
    }
    
    destroyChart(id) {
        const chart = this.charts.get(id);
        if (chart) {
            chart.destroy();
            this.charts.delete(id);
        }
    }
    
    refresh() {
        this.observeCharts();
    }
}

// Initialize lazy loaders on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize main lazy loader
    window.lazyLoader = new LazyLoader();
    
    // Initialize native lazy loading enhancement
    window.nativeLazyLoad = new NativeLazyLoad();
    
    // Initialize chart lazy loader
    window.lazyChartLoader = new LazyChartLoader();
});

// Also handle Turbo/Livewire navigation
document.addEventListener('turbo:load', () => {
    if (window.lazyLoader) {
        window.lazyLoader.refresh();
    }
    if (window.lazyChartLoader) {
        window.lazyChartLoader.refresh();
    }
});

document.addEventListener('livewire:navigated', () => {
    if (window.lazyLoader) {
        window.lazyLoader.refresh();
    }
    if (window.lazyChartLoader) {
        window.lazyChartLoader.refresh();
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { LazyLoader, NativeLazyLoad, LazyChartLoader };
}
