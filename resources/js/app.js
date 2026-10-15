import './bootstrap';

import '@fontsource-variable/inter';

import Alpine from 'alpinejs';
import AlpineCollapse from '@alpinejs/collapse';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

Alpine.plugin(AlpineCollapse);

/* ------------------------------------------------------------------
 | Palet status absensi — konsisten dengan badge di CSS.
 ------------------------------------------------------------------ */
window.SIMAS_PALETTE = {
    hadir: '#059669',
    izin: '#D5A85C',
    sakit: '#0284C7',
    alpa: '#E11D48',
    primary: '#537963',
    gold: '#D5A85C',
    muted: '#94a3b8',
};

/* ------------------------------------------------------------------
 | Preferensi tema terang/gelap (P1).
 ------------------------------------------------------------------ */
const themeStore = {
    current: 'light',

    init() {
        const stored = localStorage.getItem('simas-theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        this.apply(stored ?? (prefersDark ? 'dark' : 'light'), false);
    },

    apply(value, persist = true) {
        this.current = value === 'dark' ? 'dark' : 'light';
        document.documentElement.classList.toggle('dark', this.current === 'dark');

        if (persist) {
            localStorage.setItem('simas-theme', this.current);
        }
    },

    toggle() {
        this.apply(this.current === 'dark' ? 'light' : 'dark');
    },
};

Alpine.store('theme', {
    ...themeStore,
});

/* ------------------------------------------------------------------
 | State sidebar: collapse di desktop, drawer di mobile.
 ------------------------------------------------------------------ */
Alpine.store('sidebar', {
    mobileOpen: false,
    collapsed: localStorage.getItem('simas-sidebar') === 'collapsed',

    toggleCollapse() {
        this.collapsed = !this.collapsed;
        localStorage.setItem('simas-sidebar', this.collapsed ? 'collapsed' : 'expanded');
    },

    openMobile() {
        this.mobileOpen = true;
    },

    closeMobile() {
        this.mobileOpen = false;
    },
});

/* ------------------------------------------------------------------
 | Dialog konfirmasi global.
 | Form cukup menambahkan: data-confirm="pesan" (opsional data-confirm-title).
 ------------------------------------------------------------------ */
Alpine.data('confirmDialog', () => ({
    open: false,
    title: 'Konfirmasi',
    message: '',
    variant: 'danger',
    confirmLabel: 'Ya, lanjutkan',
    onConfirm: null,

    show(detail) {
        this.title = detail.title ?? 'Konfirmasi';
        this.message = detail.message ?? '';
        this.variant = detail.variant ?? 'danger';
        this.confirmLabel = detail.confirmLabel ?? 'Ya, lanjutkan';
        this.onConfirm = detail.onConfirm ?? null;
        this.open = true;

        this.$nextTick(() => this.$refs.confirmButton?.focus());
    },

    confirm() {
        const callback = this.onConfirm;
        this.open = false;
        callback?.();
    },

    cancel() {
        this.open = false;
        this.onConfirm = null;
    },
}));

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) {
        return;
    }

    if (form.dataset.confirmed === '1') {
        return;
    }

    event.preventDefault();

    window.dispatchEvent(
        new CustomEvent('confirm:open', {
            detail: {
                title: form.dataset.confirmTitle ?? 'Konfirmasi',
                message: form.dataset.confirm,
                variant: form.dataset.confirmVariant ?? 'danger',
                confirmLabel: form.dataset.confirmLabel ?? 'Ya, lanjutkan',
                onConfirm: () => {
                    form.dataset.confirmed = '1';
                    form.submit();
                },
            },
        })
    );
});

/* ------------------------------------------------------------------
 | Animasi angka untuk kartu statistik (menghormati reduced motion).
 ------------------------------------------------------------------ */
Alpine.data('countUp', (target = 0, duration = 700) => ({
    display: 0,

    init() {
        const value = Number(target);

        if (!Number.isFinite(value) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            this.display = Number.isFinite(value) ? value : 0;
            return;
        }

        const start = performance.now();

        const step = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            this.display = Math.round(value * eased);

            if (progress < 1) {
                requestAnimationFrame(step);
            }
        };

        requestAnimationFrame(step);
    },
}));

/* ------------------------------------------------------------------
 | Toast: tutup otomatis setelah 5 detik.
 ------------------------------------------------------------------ */
Alpine.data('toastItem', (delay = 5000) => ({
    visible: false,

    init() {
        this.$nextTick(() => (this.visible = true));

        if (delay > 0) {
            setTimeout(() => this.dismiss(), delay);
        }
    },

    dismiss() {
        this.visible = false;
        setTimeout(() => this.$el.remove(), 250);
    },
}));

/* ------------------------------------------------------------------
 | Form absensi massal (guru).
 | statuses: { [studentId]: 'hadir' | 'izin' | 'sakit' | 'alpa' }
 ------------------------------------------------------------------ */
Alpine.data('attendanceForm', (initial = {}, studentIds = []) => ({
    statuses: { ...initial },
    studentIds: studentIds,
    showNotes: false,

    get counts() {
        const counts = { hadir: 0, izin: 0, sakit: 0, alpa: 0 };

        this.studentIds.forEach((id) => {
            const status = this.statuses[id];

            if (status && Object.prototype.hasOwnProperty.call(counts, status)) {
                counts[status] += 1;
            }
        });

        return counts;
    },

    get filled() {
        return this.studentIds.filter((id) => Boolean(this.statuses[id])).length;
    },

    get total() {
        return this.studentIds.length;
    },

    get unfilled() {
        return this.total - this.filled;
    },

    get complete() {
        return this.total > 0 && this.unfilled === 0;
    },

    /** Set semua siswa ke satu status (tombol isi cepat). */
    setAll(status) {
        const next = { ...this.statuses };

        this.studentIds.forEach((id) => {
            next[id] = status;
        });

        this.statuses = next;
    },

    /** Kosongkan semua pilihan. */
    resetAll() {
        this.statuses = {};
    },
}));

/* ------------------------------------------------------------------
 | Chart.js defaults agar konsisten dengan design system.
 ------------------------------------------------------------------ */
Chart.defaults.font.family = "'Inter Variable', Inter, ui-sans-serif, system-ui, sans-serif";
Chart.defaults.font.size = 12;
Chart.defaults.borderColor = 'rgba(83, 121, 99, 0.12)';
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.boxWidth = 8;
Chart.defaults.plugins.tooltip.padding = 10;
Chart.defaults.plugins.tooltip.cornerRadius = 10;

// Hormati preferensi pengurangan gerak pengguna: tampilkan grafik secara instan.
if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    Chart.defaults.animation = false;
    Chart.defaults.animations = false;
    Chart.defaults.transitions.active.animation.duration = 0;
}

/* ------------------------------------------------------------------
 | Reveal on scroll untuk elemen .reveal.
 ------------------------------------------------------------------ */
function initReveal() {
    const elements = document.querySelectorAll('.reveal:not(.is-visible)');

    if (!elements.length) {
        return;
    }

    if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        elements.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
    );

    elements.forEach((el) => observer.observe(el));
}

document.addEventListener('DOMContentLoaded', initReveal);
document.addEventListener('alpine:initialized', initReveal);

/* ------------------------------------------------------------------
 | Sinyal kesiapan runtime frontend.
 |
 | app.js dimuat sebagai modul ES (deferred), sehingga skrip inline di
 | dalam Blade berjalan LEBIH DULU. Skrip tersebut mendaftarkan callback
 | lewat window.SIMAS_onReady(...) yang disimpan di antrean, lalu
 | dieksekusi setelah Chart.js & Alpine siap.
 ------------------------------------------------------------------ */
window.SIMAS_READY = false;
window.SIMAS_READY_QUEUE = window.SIMAS_READY_QUEUE || [];

window.SIMAS_onReady = (callback) => {
    if (window.SIMAS_READY) {
        callback();
        return;
    }

    window.SIMAS_READY_QUEUE.push(callback);
};

/* ------------------------------------------------------------------
 | Inisialisasi tema + Alpine.
 ------------------------------------------------------------------ */
window.Alpine.store('theme').init();
window.Alpine.start();

// Jalankan semua callback yang menunggu runtime siap.
window.SIMAS_READY = true;

window.SIMAS_READY_QUEUE.splice(0).forEach((callback) => {
    try {
        callback();
    } catch (error) {
        console.error('Callback SIMAS_onReady gagal dijalankan:', error);
    }
});

window.dispatchEvent(new Event('simas:ready'));
