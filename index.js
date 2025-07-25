document.addEventListener('DOMContentLoaded', function() {
    // Check session status and update auth link
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

    // Mobile Navigation Toggle (if needed for smaller screens)
    function initMobileNav() {
        const header = document.querySelector('header');
        const nav = document.querySelector('.main-nav');
        
        // Add mobile menu button if screen is small
        if (window.innerWidth <= 768) {
            if (!document.querySelector('.mobile-menu-btn')) {
                const mobileBtn = document.createElement('button');
                mobileBtn.className = 'mobile-menu-btn';
                mobileBtn.innerHTML = '☰';
                mobileBtn.style.cssText = `
                    background: none;
                    border: none;
                    font-size: 1.5rem;
                    cursor: pointer;
                    color: #4a5568;
                    display: none;
                `;
                
                header.querySelector('.header-content').appendChild(mobileBtn);
                
                mobileBtn.addEventListener('click', function() {
                    nav.style.display = nav.style.display === 'none' ? 'flex' : 'none';
                });
            }
        }
    }
    
    // Smooth scrolling for anchor links
    function initSmoothScrolling() {
        const links = document.querySelectorAll('a[href^="#"]');
        
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    const headerHeight = document.querySelector('header').offsetHeight;
                    const targetPosition = targetElement.offsetTop - headerHeight - 20;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }
    
    // Gallery lightbox effect
    function initGalleryLightbox() {
        const galleryItems = document.querySelectorAll('.gallery-item');
        
        galleryItems.forEach(item => {
            item.addEventListener('click', function() {
                const img = this.querySelector('img');
                const overlay = this.querySelector('.gallery-overlay');
                const title = overlay.querySelector('h4').textContent;
                const description = overlay.querySelector('p').textContent;
                
                // Create lightbox
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
                
                // Add lightbox styles
                lightbox.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.9);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 1000;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                `;
                
                const content = lightbox.querySelector('.lightbox-content');
                content.style.cssText = `
                    position: relative;
                    max-width: 90%;
                    max-height: 90%;
                    background: white;
                    border-radius: 15px;
                    overflow: hidden;
                    transform: scale(0.8);
                    transition: transform 0.3s ease;
                `;
                
                const closeBtn = lightbox.querySelector('.lightbox-close');
                closeBtn.style.cssText = `
                    position: absolute;
                    top: 10px;
                    right: 15px;
                    font-size: 30px;
                    color: white;
                    cursor: pointer;
                    z-index: 1001;
                    background: rgba(0, 0, 0, 0.5);
                    border-radius: 50%;
                    width: 40px;
                    height: 40px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                `;
                
                const lightboxImg = lightbox.querySelector('img');
                lightboxImg.style.cssText = `
                    width: 100%;
                    height: 400px;
                    object-fit: cover;
                `;
                
                const lightboxInfo = lightbox.querySelector('.lightbox-info');
                lightboxInfo.style.cssText = `
                    padding: 20px;
                    text-align: center;
                `;
                
                document.body.appendChild(lightbox);
                
                // Animate in
                setTimeout(() => {
                    lightbox.style.opacity = '1';
                    content.style.transform = 'scale(1)';
                }, 10);
                
                // Close functionality
                function closeLightbox() {
                    lightbox.style.opacity = '0';
                    content.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        document.body.removeChild(lightbox);
                    }, 300);
                }
                
                closeBtn.addEventListener('click', closeLightbox);
                lightbox.addEventListener('click', function(e) {
                    if (e.target === lightbox) {
                        closeLightbox();
                    }
                });
                
                // Close on escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        closeLightbox();
                    }
                });
            });
        });
    }
    
    // Animate statistics on scroll
    function initStatsAnimation() {
        const statNumbers = document.querySelectorAll('.stat-number');
        
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px 0px -100px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = entry.target;
                    const finalValue = target.textContent.replace(/\D/g, '');
                    const suffix = target.textContent.replace(/\d/g, '');
                    
                    if (finalValue) {
                        animateNumber(target, 0, parseInt(finalValue), suffix, 2000);
                    }
                    
                    observer.unobserve(target);
                }
            });
        }, observerOptions);
        
        statNumbers.forEach(stat => {
            observer.observe(stat);
        });
    }
    
    // Animate number counting
    function animateNumber(element, start, end, suffix, duration) {
        const startTime = performance.now();
        
        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const current = Math.floor(start + (end - start) * easeOutQuart(progress));
            element.textContent = current + suffix;
            
            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }
        
        requestAnimationFrame(update);
    }
    
    // Easing function for smooth animation
    function easeOutQuart(t) {
        return 1 - Math.pow(1 - t, 4);
    }
    
    // Scroll-triggered animations
    function initScrollAnimations() {
        const animatedElements = document.querySelectorAll('.service-card, .team-card, .client-item, .contact-card');
        
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.8s ease-out forwards';
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        animatedElements.forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(30px)';
            observer.observe(element);
        });
    }
    
    // Header background change on scroll
    function initHeaderScroll() {
        const header = document.querySelector('header');
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.98)';
                header.style.boxShadow = '0 2px 25px rgba(0,0,0,0.15)';
            } else {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.boxShadow = '0 2px 20px rgba(0,0,0,0.1)';
            }
        });
    }
    
    // Form validation (if contact form is added later)
    function initFormValidation() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Basic validation
                const inputs = form.querySelectorAll('input[required], textarea[required]');
                let isValid = true;
                
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        input.style.borderColor = '#ff6b6b';
                        isValid = false;
                    } else {
                        input.style.borderColor = '#ddd';
                    }
                });
                
                if (isValid) {
                    // Form submission logic would go here
                    alert('Thank you for your message! We will get back to you soon.');
                    form.reset();
                }
            });
        });
    }
    
    // Lazy loading for images
    function initLazyLoading() {
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
        
        images.forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Add CSS for animations
    function addAnimationStyles() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            @media (max-width: 768px) {
                .mobile-menu-btn {
                    display: block !important;
                }
                
                .main-nav {
                    display: none;
                    position: absolute;
                    top: 100%;
                    left: 0;
                    right: 0;
                    background: white;
                    flex-direction: column;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                    border-radius: 0 0 15px 15px;
                    padding: 20px;
                }
                
                .main-nav.active {
                    display: flex;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Initialize all functions
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
    
    // Run initialization
    init();
    
    // Re-run mobile nav on resize
    window.addEventListener('resize', initMobileNav);
    
    // Smooth page load
    window.addEventListener('load', function() {
        document.body.style.opacity = '1';
        document.body.style.transition = 'opacity 0.3s ease';
    });
});

// Set initial body opacity for smooth load
document.body.style.opacity = '0';