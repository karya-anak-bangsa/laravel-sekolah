// Entry situs publik (UniPulse, Bootstrap 5). Diadaptasi dari
// resources/templates/unipulse/assets/js/main.js dengan pustaka dari npm.
import 'bootstrap';
import AOS from 'aos';
import Swiper from 'swiper/bundle';
import GLightbox from 'glightbox';
import Isotope from 'isotope-layout';
import imagesLoaded from 'imagesloaded';
import PureCounter from '@srexi/purecounterjs';

(function () {
    'use strict';

    /** Kelas .scrolled pada body saat halaman di-scroll (header sticky). */
    function toggleScrolled() {
        const header = document.querySelector('#header');
        if (!header) return;
        if (!header.classList.contains('scroll-up-sticky') && !header.classList.contains('sticky-top') && !header.classList.contains('fixed-top')) return;
        document.body.classList.toggle('scrolled', window.scrollY > 100);
    }
    document.addEventListener('scroll', toggleScrolled);
    window.addEventListener('load', toggleScrolled);

    /** Menu mobile */
    const mobileNavToggleBtn = document.querySelector('.mobile-nav-toggle');

    function mobileNavToggle() {
        document.body.classList.toggle('mobile-nav-active');
        mobileNavToggleBtn.classList.toggle('bi-list');
        mobileNavToggleBtn.classList.toggle('bi-x');
    }
    if (mobileNavToggleBtn) {
        mobileNavToggleBtn.addEventListener('click', mobileNavToggle);
    }

    document.querySelectorAll('#navmenu a').forEach((link) => {
        link.addEventListener('click', () => {
            if (document.querySelector('.mobile-nav-active') && !link.classList.contains('toggle-dropdown')) {
                mobileNavToggle();
            }
        });
    });

    document.querySelectorAll('.navmenu .toggle-dropdown').forEach((toggle) => {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            this.parentNode.classList.toggle('active');
            this.parentNode.nextElementSibling.classList.toggle('dropdown-active');
            e.stopImmediatePropagation();
        });
    });

    /** Preloader */
    const preloader = document.querySelector('#preloader');
    if (preloader) {
        window.addEventListener('load', () => preloader.remove());
    }

    /** Tombol kembali ke atas */
    const scrollTop = document.querySelector('.scroll-top');

    function toggleScrollTop() {
        if (scrollTop) {
            scrollTop.classList.toggle('active', window.scrollY > 100);
        }
    }
    if (scrollTop) {
        scrollTop.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        window.addEventListener('load', toggleScrollTop);
        document.addEventListener('scroll', toggleScrollTop);
    }

    /** Animasi saat scroll */
    function aosInit() {
        AOS.init({ duration: 600, easing: 'ease-in-out', once: true, mirror: false });
    }
    window.addEventListener('load', aosInit);

    /** Slider Swiper (konfigurasi JSON di dalam .swiper-config) */
    window.addEventListener('load', () => {
        document.querySelectorAll('.init-swiper').forEach((el) => {
            const config = JSON.parse(el.querySelector('.swiper-config').innerHTML.trim());
            new Swiper(el, config);
        });
    });

    /** Counter angka */
    new PureCounter();

    /** Isotope (filter galeri) */
    document.querySelectorAll('.isotope-layout').forEach((isotopeItem) => {
        const layout = isotopeItem.getAttribute('data-layout') ?? 'masonry';
        const filter = isotopeItem.getAttribute('data-default-filter') ?? '*';
        const sort = isotopeItem.getAttribute('data-sort') ?? 'original-order';

        let iso;
        imagesLoaded(isotopeItem.querySelector('.isotope-container'), () => {
            iso = new Isotope(isotopeItem.querySelector('.isotope-container'), {
                itemSelector: '.isotope-item',
                layoutMode: layout,
                filter,
                sortBy: sort,
            });
        });

        isotopeItem.querySelectorAll('.isotope-filters li').forEach((item) => {
            item.addEventListener('click', function () {
                isotopeItem.querySelector('.isotope-filters .filter-active').classList.remove('filter-active');
                this.classList.add('filter-active');
                iso.arrange({ filter: this.getAttribute('data-filter') });
                aosInit();
            });
        });
    });

    /** Lightbox */
    GLightbox({ selector: '.glightbox' });
})();
