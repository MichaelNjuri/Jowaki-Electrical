document.addEventListener('DOMContentLoaded', function() {
    // Authentication Check
    function initAuthLink() {
        fetch('/jowaki_electrical_srvs/api/session-check.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const authLink = document.getElementById('auth-link');
            if (data.valid) {
                authLink.href = '/jowaki_electrical_srvs/api/Profile.php';
                authLink.textContent = '👤 Profile';
                authLink.classList.remove('login-link');
                authLink.classList.add('profile-link');
            } else {
                authLink.href = '#login';
                authLink.textContent = '👤 Login';
                authLink.classList.remove('profile-link');
                authLink.classList.add('login-link');
            }
        })
        .catch(error => {
            console.error('Error checking session:', error);
            const authLink = document.getElementById('auth-link');
            authLink.href = '#login';
            authLink.textContent = '👤 Login';
            authLink.classList.remove('profile-link');
            authLink.classList.add('login-link');
        });
    }

    // Mobile Navigation
    function initMobileNav() {
        const header = document.querySelector('header');
        const nav = document.querySelector('.main-nav');
        let mobileBtn = document.querySelector('.mobile-menu-btn');

        if (window.innerWidth <= 768) {
            if (!mobileBtn) {
                mobileBtn = document.createElement('button');
                mobileBtn.className = 'mobile-menu-btn';
                mobileBtn.innerHTML = '☰';
                mobileBtn.style.cssText = `
                    background: none;
                    border: none;
                    font-size: 1.8rem;
                    cursor: pointer;
                    color: white;
                    padding: 12px;
                    border-radius: 8px;
                    transition: all 0.3s ease;
                `;
                header.querySelector('.header-content').appendChild(mobileBtn);

                mobileBtn.addEventListener('click', function() {
                    nav.classList.toggle('active');
                    mobileBtn.innerHTML = nav.classList.contains('active') ? '×' : '☰';
                });
            }
        } else {
            nav.classList.remove('active');
            if (mobileBtn) mobileBtn.innerHTML = '☰';
        }
    }

    // Smooth Scrolling
    function initSmoothScrolling() {
        const links = document.querySelectorAll('a[href^="#"]');
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    const headerHeight = document.querySelector('header').offsetHeight;
                    window.scrollTo({
                        top: targetElement.offsetTop - headerHeight - 24,
                        behavior: 'smooth'
                    });
                    // Close mobile menu if open
                    const nav = document.querySelector('.main-nav');
                    if (nav.classList.contains('active')) {
                        nav.classList.remove('active');
                        document.querySelector('.mobile-menu-btn').innerHTML = '☰';
                    }
                }
            });
        });
    }

    // Enhanced Gallery Lightbox
    function initGalleryLightbox() {
        const galleryItems = document.querySelectorAll('.gallery-item');
        galleryItems.forEach(item => {
            item.addEventListener('click', function() {
                const img = this.querySelector('img');
                const overlay = this.querySelector('.gallery-overlay');
                const title = overlay.querySelector('h4').textContent;
                const description = overlay.querySelector('p').textContent;

                const lightbox = document.createElement('div');
                lightbox.className = 'lightbox';
                lightbox.innerHTML = `
                    <div class="lightbox-content">
                        <span class="lightbox-close">&times;</span>
                        <img src="${img.src}" alt="${img.alt}">
                        <div class="lightbox-info">
                            <h4>${title}</h4>
                            <p>${description}</p>
                        </div>
                    </div>
                `;

                lightbox.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.95);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 2000;
                    opacity: 0;
                    transition: opacity 0.4s ease;
                `;

                const content = lightbox.querySelector('.lightbox-content');
                content.style.cssText = `
                    position: relative;
                    max-width: 90%;
                    max-height: 90%;
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(12px);
                    border-radius: 20px;
                    overflow: hidden;
                    transform: scale(0.7);
                    transition: transform 0.4s ease;
                `;

                const closeBtn = lightbox.querySelector('.lightbox-close');
                closeBtn.style.cssText = `
                    position: absolute;
                    top: 16px;
                    right: 16px;
                    font-size: 36px;
                    color: white;
                    cursor: pointer;
                    background: rgba(0, 0, 0, 0.6);
                    border-radius: 50%;
                    width: 48px;
                    height: 48px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: all 0.3s ease;
                `;

                const lightboxImg = lightbox.querySelector('img');
                lightboxImg.style.cssText = `
                    width: 100%;
                    max-height: 60vh;
                    object-fit: cover;
                    border-radius: 15px 15px 0 0;
                `;

                const lightboxInfo = lightbox.querySelector('.lightbox-info');
                lightboxInfo.style.cssText = `
                    padding: 24px;
                    text-align: center;
                    background: rgba(255, 255, 255, 0.1);
                `;

                document.body.appendChild(lightbox);

                setTimeout(() => {
                    lightbox.style.opacity = '1';
                    content.style.transform = 'scale(1)';
                }, 10);

                function closeLightbox() {
                    lightbox.style.opacity = '0';
                    content.style.transform = 'scale(0.7)';
                    setTimeout(() => document.body.removeChild(lightbox), 400);
                }

                closeBtn.addEventListener('click', closeLightbox);
                lightbox.addEventListener('click', function(e) {
                    if (e.target === lightbox) closeLightbox();
                });
                document.addEventListener('keydown', function handler(e) {
                    if (e.key === 'Escape') {
                        closeLightbox();
                        document.removeEventListener('keydown', handler);
                    }
                });
            });
        });
    }

    // Animated Statistics
    function initStatsAnimation() {
        const statNumbers = document.querySelectorAll('.stat-number');
        const observerOptions = {
            threshold: 0.6,
            rootMargin: '0px 0px -80px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = entry.target;
                    const finalValue = target.textContent.replace(/\D/g, '');
                    const suffix = target.textContent.replace(/\d/g, '');
                    if (finalValue) {
                        animateNumber(target, 0, parseInt(finalValue), suffix, 2500);
                    }
                    observer.unobserve(target);
                }
            });
        }, observerOptions);

        statNumbers.forEach(stat => observer.observe(stat));
    }

    function animateNumber(element, start, end, suffix, duration) {
        const startTime = performance.now();
        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const current = Math.floor(start + (end - start) * easeOutExpo(progress));
            element.textContent = current + suffix;
            if (progress < 1) requestAnimationFrame(update);
        }
        requestAnimationFrame(update);
    }

    function easeOutExpo(t) {
        return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
    }

    // Scroll Animations
    function initScrollAnimations() {
        const animatedElements = document.querySelectorAll('.service-card, .team-card, .contact-card, .stat-item');
        const observerOptions = {
            threshold: 0.15,
            rootMargin: '0px 0px -60px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 1s ease-out forwards';
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        animatedElements.forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(40px)';
            observer.observe(element);
        });
    }

    // Header Scroll Effect
    function initHeaderScroll() {
        const header = document.querySelector('header');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 80) {
                header.style.background = 'rgba(255, 255, 255, 0.2)';
                header.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.15)';
                header.style.backdropFilter = 'blur(15px)';
            } else {
                header.style.background = 'rgba(255, 255, 255, 0.15)';
                header.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.1)';
                header.style.backdropFilter = 'blur(12px)';
            }
        });
    }

    // Form Validation
    function initFormValidation() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const inputs = form.querySelectorAll('input[required], textarea[required]');
                let isValid = true;

                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        input.style.borderColor = 'hsl(0, 76%, 60%)';
                        input.classList.add('error-shake');
                        setTimeout(() => input.classList.remove('error-shake'), 500);
                        isValid = false;
                    } else {
                        input.style.borderColor = 'hsl(151, 55%, 42%)';
                    }
                });

                if (isValid) {
                    alert('Thank you for your message! We will get back to you soon.');
                    form.reset();
                }
            });
        });
    }

    // Lazy Loading
    function initLazyLoading() {
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    img.style.transition = 'opacity 0.5s ease';
                    img.style.opacity = '1';
                    imageObserver.unobserve(img);
                }
            });
        }, { threshold: 0.1 });

        images.forEach(img => {
            img.style.opacity = '0';
            imageObserver.observe(img);
        });
    }

    // Animation Styles
    function addAnimationStyles() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(40px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes slideInDown {
                from { opacity: 0; transform: translateY(-40px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .error-shake {
                animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
            }
            @keyframes shake {
                10%, 90% { transform: translateX(-1px); }
                20%, 80% { transform: translateX(2px); }
                30%, 50%, 70% { transform: translateX(-4px); }
                40%, 60% { transform: translateX(4px); }
            }
        `;
        document.head.appendChild(style);
    }

    // Initialize
    function init() {
        addAnimationStyles();
        initAuthLink();
        initMobileNav();
        initSmoothScrolling();
        initGalleryLightbox();
        initStatsAnimation();
        initScrollAnimations();
        initHeaderScroll();
        initFormValidation();
        initLazyLoading();
    }

    init();
    window.addEventListener('resize', initMobileNav);
    window.addEventListener('load', () => {
        document.body.style.opacity = '1';
        document.body.style.transition = 'opacity 0.5s ease';
    });
});

document.body.style.opacity = '0';