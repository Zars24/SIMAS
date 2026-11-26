import Lenis from 'lenis';

const motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');

let lenis = null;
let frame = null;
let detachAnchors = null;
let anchorTimer = null;

function onAnchorClick(event) {
    if (
        event.defaultPrevented ||
        event.metaKey ||
        event.ctrlKey ||
        event.shiftKey ||
        event.altKey ||
        event.button !== 0
    ) {
        return;
    }

    const href = event.currentTarget.getAttribute('href');

    if (!href || href === '#') {
        return;
    }

    let target = null;

    try {
        target = document.querySelector(href);
    } catch {
        return;
    }

    if (!target) {
        return;
    }

    // Without Lenis, let the browser handle the anchor and respect scroll-mt.
    if (!lenis) {
        return;
    }

    event.preventDefault();

    // The mobile menu sits inside the sticky header, so its collapse changes
    // the target's position. Let Alpine finish closing it before scrolling.
    const fromMobileMenu = event.currentTarget.closest('#menu-mobile') !== null;
    const delay = fromMobileMenu ? 240 : 0;

    if (anchorTimer !== null) {
        window.clearTimeout(anchorTimer);
    }

    anchorTimer = window.setTimeout(() => {
        anchorTimer = null;

        if (!lenis) {
            target.scrollIntoView({ behavior: 'auto', block: 'start' });

            return;
        }

        // Lenis honors each section's scroll-margin-top, so the sticky
        // header offset stays consistent with native anchor behavior.
        lenis.scrollTo(target, {
            duration: 1.15,
        });

        window.history.replaceState(null, '', href);
    }, delay);
}

function attachAnchors() {
    const links = Array.from(document.querySelectorAll('a[href^="#"]:not([data-lenis-skip])'));

    links.forEach((link) => link.addEventListener('click', onAnchorClick));

    return () => {
        links.forEach((link) => link.removeEventListener('click', onAnchorClick));
    };
}

function initSmoothScroll() {
    if (lenis || motionQuery.matches) {
        return;
    }

    lenis = new Lenis({
        duration: 1.1,
        easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
        smoothWheel: true,
        wheelMultiplier: 0.9,
        touchMultiplier: 1.5,
    });

    const raf = (time) => {
        lenis.raf(time);
        frame = requestAnimationFrame(raf);
    };

    frame = requestAnimationFrame(raf);
    detachAnchors = attachAnchors();

    if (document.fonts?.ready) {
        document.fonts.ready.then(() => lenis?.resize());
    }
}

function destroySmoothScroll() {
    if (frame !== null) {
        cancelAnimationFrame(frame);
        frame = null;
    }

    if (anchorTimer !== null) {
        window.clearTimeout(anchorTimer);
        anchorTimer = null;
    }

    detachAnchors?.();
    detachAnchors = null;

    lenis?.destroy();
    lenis = null;
}

function syncMotionPreference() {
    if (motionQuery.matches) {
        destroySmoothScroll();
        document.querySelectorAll('[data-reveal]').forEach((element) => {
            element.classList.add('is-visible');
        });

        return;
    }

    initSmoothScroll();
}

function initReveal() {
    const elements = Array.from(document.querySelectorAll('[data-reveal]'));

    if (!elements.length) {
        return;
    }

    if (motionQuery.matches || !('IntersectionObserver' in window)) {
        elements.forEach((element) => element.classList.add('is-visible'));

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        },
        {
            threshold: 0.12,
            rootMargin: '0px 0px -8% 0px',
        }
    );

    elements.forEach((element) => observer.observe(element));
}

initReveal();
initSmoothScroll();

if (motionQuery.addEventListener) {
    motionQuery.addEventListener('change', syncMotionPreference);
} else {
    motionQuery.addListener(syncMotionPreference);
}
