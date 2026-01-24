/**
 * Landing Page Interactions - Hukum Ngoerah
 * Stripe-inspired animations and effects
 */

// =============================================================================
// Intersection Observer for Scroll Animations (Two-way - repeats on scroll)
// =============================================================================
function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function shouldDisableMotionEffects() {
    return prefersReducedMotion()
        || window.matchMedia('(pointer: coarse)').matches
        || window.matchMedia('(max-width: 768px)').matches;
}

function initScrollReveal() {
    const revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale');

    if (revealElements.length === 0) return;

    if (prefersReducedMotion()) {
        revealElements.forEach(el => el.classList.add('active'));
        return;
    }

    const observerOptions = {
        root: null,
        rootMargin: '-50px 0px -50px 0px',
        threshold: 0.15
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Add active class when entering viewport
                entry.target.classList.add('active');
            } else {
                // Remove active class when leaving viewport (enables repeat)
                entry.target.classList.remove('active');
            }
        });
    }, observerOptions);

    revealElements.forEach(el => observer.observe(el));
}

// =============================================================================
// Number Counter Animation
// =============================================================================
function animateCounter(element, target, duration = 2000, suffix = '') {
    const start = 0;
    const startTime = performance.now();

    function easeOutQuart(t) {
        return 1 - Math.pow(1 - t, 4);
    }

    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const easedProgress = easeOutQuart(progress);
        const current = Math.floor(easedProgress * target);

        element.textContent = current.toLocaleString('id-ID') + suffix;

        if (progress < 1) {
            requestAnimationFrame(update);
        } else {
            element.textContent = target.toLocaleString('id-ID') + suffix;
        }
    }

    requestAnimationFrame(update);
}

function initCounters() {
    const counters = document.querySelectorAll('[data-counter]');

    if (counters.length === 0) return;

    if (prefersReducedMotion()) {
        counters.forEach(counter => {
            const target = parseInt(counter.dataset.counter, 10);
            const suffix = counter.dataset.counterSuffix || '';
            counter.textContent = target.toLocaleString('id-ID') + suffix;
        });
        return;
    }

    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.5
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Only animate if not currently animating
                if (!entry.target.dataset.animating) {
                    const target = parseInt(entry.target.dataset.counter, 10);
                    const suffix = entry.target.dataset.counterSuffix || '';
                    const duration = parseInt(entry.target.dataset.counterDuration, 10) || 2000;

                    entry.target.dataset.animating = 'true';
                    animateCounter(entry.target, target, duration, suffix);

                    // Reset after animation completes
                    setTimeout(() => {
                        entry.target.dataset.animating = '';
                    }, duration + 100);
                }
            } else {
                // Reset counter when leaving viewport for next animation
                entry.target.textContent = '0';
                entry.target.dataset.animating = '';
            }
        });
    }, observerOptions);

    counters.forEach(el => {
        el.textContent = '0';
        observer.observe(el);
    });
}

// =============================================================================
// Connection Lines Animation (CSS-based How It Works)
// =============================================================================
function initConnectionLines() {
    const stepsContainer = document.querySelector('.steps-container');
    if (!stepsContainer) return;

    const dots = stepsContainer.querySelectorAll('.connection-dot');

    const observerOptions = {
        root: null,
        rootMargin: '-50px 0px -50px 0px',
        threshold: 0.2
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Start dot animation when section is visible
                dots.forEach(dot => dot.style.animationPlayState = 'running');
            } else {
                // Pause animation when not visible
                dots.forEach(dot => dot.style.animationPlayState = 'paused');
            }
        });
    }, observerOptions);

    // Initially pause animations
    dots.forEach(dot => dot.style.animationPlayState = 'paused');

    observer.observe(stepsContainer);
}

// =============================================================================
// Parallax Effect
// =============================================================================
function initParallax() {
    const parallaxElements = document.querySelectorAll('[data-parallax]');

    if (parallaxElements.length === 0) return;

    if (shouldDisableMotionEffects()) return;

    let ticking = false;

    function updateParallax() {
        const scrollY = window.scrollY;

        parallaxElements.forEach(el => {
            const speed = parseFloat(el.dataset.parallax) || 0.5;
            const yPos = -(scrollY * speed);
            el.style.transform = `translate3d(0, ${yPos}px, 0)`;
        });

        ticking = false;
    }

    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(updateParallax);
            ticking = true;
        }
    }, { passive: true });
}

// =============================================================================
// FAQ Accordion
// =============================================================================
function initFaqAccordion() {
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(item => {
        const trigger = item.querySelector('.faq-trigger');

        if (trigger) {
            trigger.addEventListener('click', () => {
                const isOpen = item.classList.contains('open');

                // Close all other items
                faqItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('open');
                    }
                });

                // Toggle current item
                item.classList.toggle('open', !isOpen);
            });
        }
    });
}

// =============================================================================
// Smooth Scroll for Anchor Links
// =============================================================================
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: prefersReducedMotion() ? 'auto' : 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// =============================================================================
// Navbar Background on Scroll
// =============================================================================
function initNavbarScroll() {
    const navbar = document.querySelector('[data-navbar]');

    if (!navbar) return;

    let lastScroll = 0;

    window.addEventListener('scroll', () => {
        const currentScroll = window.scrollY;

        if (currentScroll > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }

        lastScroll = currentScroll;
    }, { passive: true });
}

// =============================================================================
// Mouse Move Effect for Hero (subtle)
// =============================================================================
function initHeroMouseEffect() {
    const hero = document.querySelector('[data-hero-interactive]');

    if (!hero) return;

    if (shouldDisableMotionEffects()) return;

    const blobs = hero.querySelectorAll('.blob');

    hero.addEventListener('mousemove', (e) => {
        const rect = hero.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width - 0.5;
        const y = (e.clientY - rect.top) / rect.height - 0.5;

        blobs.forEach((blob, index) => {
            const factor = (index + 1) * 20;
            const translateX = x * factor;
            const translateY = y * factor;

            blob.style.transform = `translate(${translateX}px, ${translateY}px)`;
        });
    });

    hero.addEventListener('mouseleave', () => {
        blobs.forEach(blob => {
            blob.style.transform = 'translate(0, 0)';
        });
    });
}

// =============================================================================
// Theme Toggle Enhancement
// =============================================================================
function initThemeToggle() {
    const toggleBtn = document.querySelector('[data-theme-toggle]');

    if (!toggleBtn) return;

    toggleBtn.addEventListener('click', () => {
        // The actual toggle is handled by darkMode.toggle()
        // This just adds a small animation feedback
        toggleBtn.style.transform = 'scale(0.9)';
        setTimeout(() => {
            toggleBtn.style.transform = 'scale(1)';
        }, 150);
    });
}

// =============================================================================
// Mobile Menu
// =============================================================================
function initMobileMenu() {
    const toggleBtn = document.querySelector('[data-mobile-menu-toggle]');
    const menu = document.querySelector('[data-mobile-menu]');

    if (!toggleBtn || !menu) return;

    function openMenu() {
        menu.hidden = false;
        menu.classList.add('open');
        toggleBtn.setAttribute('aria-expanded', 'true');
    }

    function closeMenu() {
        menu.hidden = true;
        menu.classList.remove('open');
        toggleBtn.setAttribute('aria-expanded', 'false');
    }

    function toggleMenu() {
        if (menu.hidden) {
            openMenu();
        } else {
            closeMenu();
        }
    }

    toggleBtn.addEventListener('click', (event) => {
        event.stopPropagation();
        toggleMenu();
    });

    menu.addEventListener('click', (event) => {
        if (event.target.closest('a')) {
            closeMenu();
        }
    });

    document.addEventListener('click', (event) => {
        if (!menu.contains(event.target) && !toggleBtn.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            closeMenu();
        }
    });
}

// =============================================================================
// Typing Effect (optional for hero headline)
// =============================================================================
function initTypingEffect() {
    const typingElement = document.querySelector('[data-typing]');

    if (!typingElement) return;

    const words = typingElement.dataset.typing.split(',');
    let wordIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    let typeSpeed = 100;

    function type() {
        const currentWord = words[wordIndex];

        if (isDeleting) {
            typingElement.textContent = currentWord.substring(0, charIndex - 1);
            charIndex--;
            typeSpeed = 50;
        } else {
            typingElement.textContent = currentWord.substring(0, charIndex + 1);
            charIndex++;
            typeSpeed = 100;
        }

        if (!isDeleting && charIndex === currentWord.length) {
            typeSpeed = 2000; // Pause at end
            isDeleting = true;
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            wordIndex = (wordIndex + 1) % words.length;
            typeSpeed = 500; // Pause before next word
        }

        setTimeout(type, typeSpeed);
    }

    type();
}

// =============================================================================
// Button Ripple Effect
// =============================================================================
function initButtonRipple() {
    const buttons = document.querySelectorAll('.btn-primary-landing, .btn-secondary-landing');

    buttons.forEach(button => {
        button.addEventListener('click', function (e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const ripple = document.createElement('span');
            ripple.style.cssText = `
                position: absolute;
                width: 0;
                height: 0;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.3);
                transform: translate(-50%, -50%);
                left: ${x}px;
                top: ${y}px;
                animation: ripple 0.6s ease-out forwards;
            `;

            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);

            setTimeout(() => ripple.remove(), 600);
        });
    });

    // Add ripple animation if not exists
    if (!document.querySelector('#ripple-style')) {
        const style = document.createElement('style');
        style.id = 'ripple-style';
        style.textContent = `
            @keyframes ripple {
                to {
                    width: 300px;
                    height: 300px;
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

// =============================================================================
// Initialize All
// =============================================================================
function initLanding() {
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        initScrollReveal();
        initCounters();
        initConnectionLines();
        initParallax();
        initFaqAccordion();
        initSmoothScroll();
        initNavbarScroll();
        initHeroMouseEffect();
        initThemeToggle();
        initMobileMenu();
        initButtonRipple();
        // initTypingEffect(); // Enable if needed

        console.log('🚀 Landing page initialized');
    }
}

// Auto-initialize
initLanding();

// Export for manual use if needed
window.LandingPage = {
    initScrollReveal,
    initCounters,
    initParallax,
    initFaqAccordion,
    animateCounter
};
