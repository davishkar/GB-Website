document.addEventListener('DOMContentLoaded', () => {

    // --- 1. Language Handling ---
    const langSelector = document.getElementById('lang-selector');
    const defaultLang = 'mr';
    let currentLang = localStorage.getItem('preferredLang') || defaultLang;

    if (langSelector) {
        langSelector.value = currentLang;
        langSelector.addEventListener('change', (e) => {
            currentLang = e.target.value;
            localStorage.setItem('preferredLang', currentLang);
            loadLanguage(currentLang);
        });
    }
    loadLanguage(currentLang);

    async function loadLanguage(lang) {
        try {
            const response = await fetch(`lang/${lang}.json`);
            if (!response.ok) throw new Error(`Could not load ${lang}.json`);
            const translations = await response.json();
            applyTranslations(translations);
            document.documentElement.lang = lang;
            document.documentElement.dir = ['ur', 'ar', 'he'].includes(lang) ? 'rtl' : 'ltr';
        } catch (error) {
            console.error('Error loading language:', error);
        }
    }

    function applyTranslations(translations) {
        document.querySelectorAll('[data-i18n]').forEach(element => {
            const key = element.getAttribute('data-i18n');
            if (translations[key]) {
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    element.placeholder = translations[key];
                } else {
                    element.textContent = translations[key];
                }
            }
        });
    }


    // --- 2. Loader ---
    const loader = document.querySelector('.loader');
    if (loader) {
        setTimeout(() => loader.classList.add('hidden'), 800);
    }


    // --- 3. Vanta Animated Background ---
    const vantaBg = document.getElementById('vanta-bg');
    if (vantaBg && typeof VANTA !== 'undefined') {
        VANTA.DOTS({
            el: vantaBg,
            mouseControls: true,
            touchControls: true,
            gyroControls: false,
            minHeight: 200,
            minWidth: 200,
            scale: 1.0,
            scaleMobile: 1.0,
            color: 0xd4af37,       // Gold
            color2: 0xb8860b,      // Dark gold
            backgroundColor: 0x0b0b0f,
            size: 4.5,
            spacing: 60,
            showLines: true
        });
    }


    // --- 4. Navbar Scroll Effect ---
    const header = document.querySelector('header');
    if (header) {
        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 50);
        });
    }


    // --- 5. Mobile Menu ---
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('navLinks');
    const navClose = document.getElementById('navClose');

    function openMenu() {
        if (navLinks) navLinks.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeMenu() {
        if (navLinks) navLinks.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (hamburger) hamburger.addEventListener('click', openMenu);
    if (navClose) navClose.addEventListener('click', closeMenu);

    // Close menu when a nav link is clicked
    if (navLinks) {
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', closeMenu);
        });
    }

    // Close menu on outside click
    document.addEventListener('click', (e) => {
        if (navLinks && navLinks.classList.contains('active')) {
            if (!navLinks.contains(e.target) && !hamburger.contains(e.target)) {
                closeMenu();
            }
        }
    });


    // --- 6. Active Nav Link Highlighting ---
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    document.querySelectorAll('.nav-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });


    // --- 7. Scroll Fade-Up Animations ---
    const fadeObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                fadeObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.08,
        rootMargin: '0px 0px -40px 0px'
    });

    document.querySelectorAll('.fade-up').forEach(el => {
        fadeObserver.observe(el);
    });


    // --- 8. Gallery Lightbox ---
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const lightboxCaption = document.getElementById('lightbox-caption');
    const closeBtn = document.querySelector('.lightbox-close');

    if (lightbox) {
        document.querySelectorAll('.gallery-item img').forEach(img => {
            img.style.cursor = 'pointer';
            img.addEventListener('click', () => {
                lightbox.classList.add('active');
                lightboxImg.src = img.src;
                const captionEl = img.nextElementSibling;
                lightboxCaption.textContent = captionEl ? captionEl.textContent : img.alt;
                document.body.style.overflow = 'hidden';
            });
        });

        function closeLightbox() {
            lightbox.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) closeLightbox();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeLightbox();
        });
    }


    // --- 9. Business Card 3D Flip (Touch Support) ---
    const card3d = document.querySelector('.card-3d');
    if (card3d) {
        card3d.addEventListener('click', function () {
            this.classList.toggle('flipped');
        });
    }

});
