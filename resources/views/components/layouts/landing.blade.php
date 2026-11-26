@props(['title' => null, 'description' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $description ?? 'SIMAS — Sistem Informasi Manajemen Absensi Siswa. Catat kehadiran lebih cepat, kelola data sekolah lebih teratur.' }}">
    <meta name="color-scheme" content="light dark">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }} — Manajemen Absensi Siswa</title>

    <script>
        document.documentElement.classList.add('js');

        (function () {
            try {
                var stored = localStorage.getItem('simas-theme');
                var theme = stored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                if (theme === 'dark') document.documentElement.classList.add('dark');
            } catch (e) {}
        })();
    </script>

    @vite([
        'resources/css/app.css',
        'resources/css/landing.css',
        'resources/js/app.js',
        'resources/js/landing.js',
    ])
</head>
<body class="h-full lp-body">
    <a href="#konten" data-lenis-skip class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-md focus:bg-[color:var(--lp-btn-bg)] focus:px-4 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-[color:var(--lp-btn-fg)]">
        Lewati ke konten utama
    </a>

    {{-- Navbar --}}
    <header class="lp-nav" x-data="{ open: false }" @keydown.escape.window="open = false">
        <nav class="lp-container flex h-[4.5rem] items-center gap-6" aria-label="Navigasi utama">
            <a href="{{ route('landing') }}" class="flex items-center gap-3 rounded-sm">
                <span class="lp-brand-mark">
                    <x-icon name="academic-cap" class="h-[1.15rem] w-[1.15rem]" />
                </span>
                <span class="flex flex-col">
                    <span class="lp-wordmark">SIMAS</span>
                    <span class="lp-brand-sub mt-1 hidden sm:block">Absensi Sekolah</span>
                </span>
            </a>

            <div class="ml-4 hidden items-center gap-7 md:flex">
                <a href="#fitur" class="lp-nav-link">Fitur</a>
                <a href="#cara-kerja" class="lp-nav-link">Cara Kerja</a>
                <a href="#peran" class="lp-nav-link">Peran</a>
                <a href="#faq" class="lp-nav-link">FAQ</a>
            </div>

            <div class="ml-auto flex items-center gap-2">
                <button type="button" @click="$store.theme.toggle()"
                        class="lp-icon-btn"
                        aria-label="Ganti tema terang atau gelap"
                        :aria-pressed="$store.theme.current === 'dark'">
                    <x-icon name="sun" class="hidden h-[1.05rem] w-[1.05rem] dark:block" />
                    <x-icon name="moon" class="h-[1.05rem] w-[1.05rem] dark:hidden" />
                </button>

                @auth
                    <a href="{{ route('dashboard') }}" class="lp-btn lp-btn-primary lp-btn-sm">Buka Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="lp-btn lp-btn-primary lp-btn-sm">
                        Masuk
                        <x-icon name="arrow-right-on-rectangle" class="lp-arrow hidden h-4 w-4 sm:block" />
                    </a>
                @endauth

                <button type="button" @click="open = !open"
                        class="lp-icon-btn lp-menu-btn"
                        :aria-expanded="open"
                        :aria-label="open ? 'Tutup menu' : 'Buka menu'"
                        aria-controls="menu-mobile">
                    <x-icon name="bars-3" class="h-5 w-5" x-show="!open" />
                    <x-icon name="x-mark" class="h-5 w-5" x-cloak x-show="open" />
                </button>
            </div>
        </nav>

        {{-- Menu mobile --}}
        <div id="menu-mobile" x-cloak x-show="open" x-collapse.duration.200ms
             class="border-t border-[color:var(--lp-line)] md:hidden">
            <div class="lp-container py-2">
                <a href="#fitur" @click="open = false" class="lp-mobile-link">Fitur</a>
                <a href="#cara-kerja" @click="open = false" class="lp-mobile-link">Cara Kerja</a>
                <a href="#peran" @click="open = false" class="lp-mobile-link">Peran</a>
                <a href="#faq" @click="open = false" class="lp-mobile-link">FAQ</a>
            </div>
        </div>
    </header>

    <main id="konten" tabindex="-1">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="lp-footer">
        <div class="lp-container py-14">
            <div class="grid gap-10 md:grid-cols-12 md:gap-8">
                <div class="md:col-span-6">
                    <a href="{{ route('landing') }}" class="inline-flex items-center gap-3">
                        <span class="lp-brand-mark">
                            <x-icon name="academic-cap" class="h-[1.15rem] w-[1.15rem]" />
                        </span>
                        <span class="flex flex-col">
                            <span class="lp-wordmark">SIMAS</span>
                            <span class="lp-brand-sub mt-1">Absensi Sekolah</span>
                        </span>
                    </a>
                    <p class="mt-5 max-w-sm text-sm leading-relaxed text-[color:var(--lp-muted)]">
                        Sistem Informasi Manajemen Absensi Siswa. Dibangun dengan Laravel, MySQL, dan Tailwind CSS
                        sebagai proyek portofolio full-stack — fokus pada keamanan, integritas data, dan pengalaman pengguna.
                    </p>
                </div>

                <div class="md:col-span-3">
                    <p class="lp-footer-heading">Navigasi</p>
                    <ul class="mt-4 space-y-2.5">
                        <li><a href="#fitur" class="lp-footer-link">Fitur</a></li>
                        <li><a href="#cara-kerja" class="lp-footer-link">Cara Kerja</a></li>
                        <li><a href="#peran" class="lp-footer-link">Peran Pengguna</a></li>
                        <li><a href="#faq" class="lp-footer-link">FAQ</a></li>
                    </ul>
                </div>

                <div class="md:col-span-3">
                    <p class="lp-footer-heading">Mulai</p>
                    <ul class="mt-4 space-y-2.5">
                        <li><a href="{{ route('login') }}" class="lp-footer-link">Masuk ke aplikasi</a></li>
                        <li><span class="lp-footer-link">Akun demo tersedia di halaman masuk</span></li>
                    </ul>
                </div>
            </div>

            <div class="lp-footer-meta mt-12">
                <p>&copy; {{ date('Y') }} SIMAS. Proyek portofolio — seluruh data adalah contoh non-sensitif.</p>
                <p class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="bolt" class="h-3.5 w-3.5 text-[color:var(--lp-accent)]" />
                        Laravel 10
                    </span>
                    <span aria-hidden="true">·</span>
                    <span>MySQL</span>
                    <span aria-hidden="true">·</span>
                    <span>Tailwind CSS</span>
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
