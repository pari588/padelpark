/**
 * Sky Padel India - Interactive JavaScript
 * Smooth Parallax, Scroll Animations & Form Handling
 */

(function() {
    'use strict';

    // =================================
    // CONFIGURATION
    // =================================
    const CONFIG = {
        parallaxSpeed: 0.3,
        scrollThreshold: 50,
        animationOffset: 100,
        mobileBreakpoint: 992
    };

    // =================================
    // NAVIGATION
    // =================================
    const nav = document.getElementById('mainNav');
    const mobileNav = document.getElementById('mobileNav');

    function handleNavScroll() {
        if (window.scrollY > CONFIG.scrollThreshold) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    }

    window.toggleMobileNav = function() {
        mobileNav.classList.toggle('active');
        document.body.style.overflow = mobileNav.classList.contains('active') ? 'hidden' : '';
    };

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;

            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                const navHeight = nav.offsetHeight;
                const targetPosition = target.getBoundingClientRect().top + window.scrollY - navHeight - 20;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // =================================
    // PARALLAX EFFECTS
    // =================================
    const parallaxElements = document.querySelectorAll('[data-parallax]');

    function updateParallax() {
        const scrollY = window.scrollY;

        // Set CSS variable for scroll position
        document.documentElement.style.setProperty('--scroll', scrollY);

        parallaxElements.forEach(el => {
            const speed = parseFloat(el.dataset.speed) || CONFIG.parallaxSpeed;
            const rect = el.getBoundingClientRect();
            const visible = rect.bottom > 0 && rect.top < window.innerHeight;

            if (visible) {
                const yPos = (scrollY - el.offsetTop) * speed;
                el.style.transform = `translateY(${yPos}px)`;
            }
        });
    }

    // =================================
    // SCROLL REVEAL ANIMATIONS
    // =================================
    const revealElements = document.querySelectorAll('.reveal');

    function checkReveal() {
        const windowHeight = window.innerHeight;
        const revealPoint = CONFIG.animationOffset;

        revealElements.forEach(el => {
            const elementTop = el.getBoundingClientRect().top;
            const delay = parseFloat(el.dataset.delay) || 0;

            if (elementTop < windowHeight - revealPoint) {
                setTimeout(() => {
                    el.classList.add('revealed');
                }, delay * 1000);
            }
        });
    }

    // =================================
    // FLOATING ELEMENTS ANIMATION
    // =================================
    function initFloatingElements() {
        const floatBalls = document.querySelectorAll('.float-ball');

        floatBalls.forEach((ball, index) => {
            // Add slight random movement
            const randomDelay = Math.random() * 2;
            ball.style.animationDelay = `${randomDelay}s`;
        });
    }

    // =================================
    // HERO COURT LINES ANIMATION
    // =================================
    function animateCourtLines() {
        const courtLines = document.querySelector('.hero-court-lines');
        if (!courtLines) return;

        // Create additional animated lines
        for (let i = 0; i < 5; i++) {
            const line = document.createElement('div');
            line.style.cssText = `
                position: absolute;
                background: rgba(255, 255, 255, 0.05);
                animation: courtLineFloat ${8 + i * 2}s ease-in-out infinite;
                animation-delay: ${i * 0.5}s;
            `;

            if (i % 2 === 0) {
                line.style.width = '1px';
                line.style.height = '100%';
                line.style.left = `${20 + i * 15}%`;
            } else {
                line.style.width = '100%';
                line.style.height = '1px';
                line.style.top = `${20 + i * 15}%`;
            }

            courtLines.appendChild(line);
        }

        // Add CSS animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes courtLineFloat {
                0%, 100% { opacity: 0.05; transform: translateX(0) translateY(0); }
                50% { opacity: 0.1; transform: translateX(10px) translateY(10px); }
            }
        `;
        document.head.appendChild(style);
    }

    // =================================
    // STATS COUNTER ANIMATION
    // =================================
    function animateStats() {
        const statValues = document.querySelectorAll('.hero-stat-value');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateNumber(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        statValues.forEach(stat => observer.observe(stat));
    }

    function animateNumber(el) {
        const text = el.textContent;
        const hasPlus = text.includes('+');
        const hasStar = text.includes('*');
        const numberMatch = text.match(/\d+/);

        if (!numberMatch) return;

        const targetNumber = parseInt(numberMatch[0]);
        const duration = 2000;
        const startTime = performance.now();
        const startNumber = 0;

        function updateNumber(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Easing function (ease-out)
            const easeProgress = 1 - Math.pow(1 - progress, 3);
            const currentNumber = Math.floor(startNumber + (targetNumber - startNumber) * easeProgress);

            el.textContent = currentNumber + (hasPlus ? '+' : '') + (hasStar ? '*' : '');

            if (progress < 1) {
                requestAnimationFrame(updateNumber);
            }
        }

        requestAnimationFrame(updateNumber);
    }

    // =================================
    // CONTACT FORM
    // =================================
    const contactForm = document.getElementById('contactForm');
    const submitBtn = document.getElementById('submitBtn');
    const formMessage = document.getElementById('formMessage');

    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="btn-icon" style="animation: spin 1s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" stroke-dasharray="30 60"/>
                </svg>
                Submitting...
            `;

            // Add spin animation
            const style = document.createElement('style');
            style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
            document.head.appendChild(style);

            const formData = new FormData(this);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                formMessage.style.display = 'block';

                if (data.err === 0) {
                    formMessage.style.background = 'var(--sp-turf-100)';
                    formMessage.style.color = 'var(--sp-turf-700)';
                    formMessage.textContent = data.msg;
                    contactForm.reset();
                } else {
                    formMessage.style.background = '#FEE2E2';
                    formMessage.style.color = '#DC2626';
                    formMessage.textContent = data.msg;
                }

                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = `
                    Submit Inquiry
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
                    </svg>
                `;
            })
            .catch(error => {
                formMessage.style.display = 'block';
                formMessage.style.background = '#FEE2E2';
                formMessage.style.color = '#DC2626';
                formMessage.textContent = 'An error occurred. Please try again.';

                submitBtn.disabled = false;
                submitBtn.innerHTML = `
                    Submit Inquiry
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
                    </svg>
                `;
            });
        });
    }

    // =================================
    // PROJECT CARD INTERACTIONS
    // =================================
    function initProjectCards() {
        const cards = document.querySelectorAll('.project-card');

        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });
    }

    // =================================
    // FEATURE CARD INTERACTIONS
    // =================================
    function initFeatureCards() {
        const cards = document.querySelectorAll('.feature-card');

        cards.forEach(card => {
            card.addEventListener('mouseenter', function(e) {
                // Add subtle glow effect
                this.style.boxShadow = '0 20px 40px rgba(0, 119, 182, 0.15)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.boxShadow = '';
            });
        });
    }

    // =================================
    // PROCESS STEP INTERACTIONS
    // =================================
    function initProcessSteps() {
        const steps = document.querySelectorAll('.process-step');

        steps.forEach((step, index) => {
            step.addEventListener('mouseenter', function() {
                // Pulse the connected line
                const number = this.querySelector('.process-step-number');
                number.style.animation = 'none';
                setTimeout(() => {
                    number.style.animation = 'pulse 0.5s ease-out';
                }, 10);
            });
        });
    }

    // =================================
    // TILT EFFECT FOR CARDS (Optional)
    // =================================
    function initTiltEffect() {
        if (window.innerWidth < CONFIG.mobileBreakpoint) return;

        const tiltCards = document.querySelectorAll('.project-card, .about-image-main');

        tiltCards.forEach(card => {
            card.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                const centerX = rect.width / 2;
                const centerY = rect.height / 2;

                const rotateX = (y - centerY) / 20;
                const rotateY = (centerX - x) / 20;

                this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.02)`;
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });
    }

    // =================================
    // SCROLL PROGRESS INDICATOR (Optional)
    // =================================
    function initScrollProgress() {
        const progressBar = document.createElement('div');
        progressBar.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--sp-court-500), var(--sp-turf-500));
            z-index: 9999;
            transition: width 0.1s;
        `;
        document.body.appendChild(progressBar);

        function updateProgress() {
            const scrollTop = window.scrollY;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const progress = (scrollTop / docHeight) * 100;
            progressBar.style.width = `${progress}%`;
        }

        window.addEventListener('scroll', updateProgress);
    }

    // =================================
    // LAZY LOAD IMAGES (Optional)
    // =================================
    function initLazyLoad() {
        const images = document.querySelectorAll('img[data-src]');

        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    }

    // =================================
    // THROTTLE UTILITY
    // =================================
    function throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    // =================================
    // INITIALIZE
    // =================================
    function init() {
        // Navigation scroll handler
        window.addEventListener('scroll', throttle(handleNavScroll, 10));
        handleNavScroll(); // Initial check

        // Parallax effects
        window.addEventListener('scroll', throttle(updateParallax, 16));
        updateParallax(); // Initial position

        // Reveal animations
        window.addEventListener('scroll', throttle(checkReveal, 100));
        checkReveal(); // Initial check

        // Initialize components
        initFloatingElements();
        animateCourtLines();
        animateStats();
        initProjectCards();
        initFeatureCards();
        initProcessSteps();
        initTiltEffect();
        initScrollProgress();
        initLazyLoad();

        // Handle resize
        window.addEventListener('resize', throttle(() => {
            if (window.innerWidth >= CONFIG.mobileBreakpoint && mobileNav.classList.contains('active')) {
                toggleMobileNav();
            }
        }, 250));

        console.log('Sky Padel - Website initialized');
    }

    // Start when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
