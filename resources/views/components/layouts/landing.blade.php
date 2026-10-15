@props(['title' => null, 'description' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $description ?? 'SIMAS — Sistem Informasi Manajemen Absensi Siswa. Catat kehadiran lebih cepat, kelola data sekolah lebih teratur.' }}">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }} — Manajemen Absensi Siswa</title>

    <script>
        (function () {
            try {
                var stored = localStorage.getItem('simas-theme');
                var theme = stored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                if (theme === 'dark') document.documentElement.classList.add('dark');
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-xl focus:bg-primary-500 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
        Lewati ke konten utama
    </a>

    {{-- Navbar --}}
    <header class="sticky top-0 z-40 border-b border-primary-900/[0.06] bg-cream/85 backdrop-blur dark:border-white/10 dark:bg-primary-950/85" x-data="{ open: false }">
        <nav class="mx-auto flex h-16 w-full max-w-7xl items-center gap-4 px-4 sm:px-6 lg:px-8" aria-label="Navigasi utama">
            <a href="{{ route('landing') }}" class="flex items-center gap-2.5 rounded-xl">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-500 text-white shadow-sm">
                    <x-icon name="academic-cap" class="h-5 w-5" />
                </span>
                <span class="text-lg font-bold tracking-tight text-primary-900 dark:text-white">SIMAS</span>
            </a>

            <div class="ml-6 hidden items-center gap-1 md:flex">
                <a href="#fitur" class="rounded-xl px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-primary-100/70 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-primary-900/60 dark:hover:text-white">Fitur</a>
                <a href="#cara-kerja" class="rounded-xl px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-primary-100/70 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-primary-900/60 dark:hover:text-white">Cara Kerja</a>
                <a href="#peran" class="rounded-xl px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-primary-100/70 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-primary-900/60 dark:hover:text-white">Peran</a>
                <a href="#faq" class="rounded-xl px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-primary-100/70 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-primary-900/60 dark:hover:text-white">FAQ</a>
            </div>

            <div class="ml-auto flex items-center gap-2">
                <button type="button" @click="$store.theme.toggle()"
                        class="rounded-xl p-2 text-primary-700 transition hover:bg-primary-100 dark:text-primary-200 dark:hover:bg-primary-900/60"
                        aria-label="Ganti tema terang atau gelap">
                    <x-icon name="sun" class="hidden h-5 w-5 dark:block" />
                    <x-icon name="moon" class="h-5 w-5 dark:hidden" />
                </button>

                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary btn-sm sm:px-4 sm:py-2.5">Buka Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-primary btn-sm sm:px-4 sm:py-2.5">
                        Masuk
                        <x-icon name="arrow-right-on-rectangle" class="h-4 w-4" />
                    </a>
                @endauth

                <button type="button" @click="open = !open" class="rounded-xl p-2 text-primary-700 transition hover:bg-primary-100 dark:text-primary-200 dark:hover:bg-primary-900/60 md:hidden" :aria-expanded="open" aria-controls="menu-mobile" aria-label="Buka menu">
                    <x-icon name="bars-3" class="h-5 w-5" x-show="!open" />
                    <x-icon name="x-mark" class="h-5 w-5" x-cloak x-show="open" />
                </button>
            </div>
        </nav>

        {{-- Menu mobile --}}
        <div id="menu-mobile" x-cloak x-show="open" x-collapse.duration.200ms class="border-t border-primary-900/[0.06] bg-cream dark:border-white/10 dark:bg-primary-950 md:hidden">
            <div class="space-y-1 px-4 py-3">
                <a href="#fitur" @click="open = false" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-primary-100/70 dark:text-slate-200 dark:hover:bg-primary-900/60">Fitur</a>
                <a href="#cara-kerja" @click="open = false" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-primary-100/70 dark:text-slate-200 dark:hover:bg-primary-900/60">Cara Kerja</a>
                <a href="#peran" @click="open = false" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-primary-100/70 dark:text-slate-200 dark:hover:bg-primary-900/60">Peran</a>
                <a href="#faq" @click="open = false" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-primary-100/70 dark:text-slate-200 dark:hover:bg-primary-900/60">FAQ</a>
            </div>
        </div>
    </header>

    <main id="konten">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="border-t border-primary-900/[0.06] bg-white dark:border-white/10 dark:bg-primary-950">
        <div class="mx-auto w-full max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-10 md:grid-cols-4">
                <div class="md:col-span-2">
                    <a href="{{ route('landing') }}" class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-500 text-white">
                            <x-icon name="academic-cap" class="h-5 w-5" />
                        </span>
                        <span class="text-lg font-bold tracking-tight text-primary-900 dark:text-white">SIMAS</span>
                    </a>
                    <p class="mt-4 max-w-md text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                        Sistem Informasi Manajemen Absensi Siswa. Dibangun dengan Laravel, MySQL, dan Tailwind CSS
                        sebagai proyek portofolio full-stack — fokus pada keamanan, integritas data, dan pengalaman pengguna.
                    </p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-primary-900 dark:text-white">Navigasi</p>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li><a href="#fitur" class="text-slate-500 transition hover:text-primary-700 dark:text-slate-400 dark:hover:text-primary-200">Fitur</a></li>
                        <li><a href="#cara-kerja" class="text-slate-500 transition hover:text-primary-700 dark:text-slate-400 dark:hover:text-primary-200">Cara Kerja</a></li>
                        <li><a href="#peran" class="text-slate-500 transition hover:text-primary-700 dark:text-slate-400 dark:hover:text-primary-200">Peran Pengguna</a></li>
                        <li><a href="#faq" class="text-slate-500 transition hover:text-primary-700 dark:text-slate-400 dark:hover:text-primary-200">FAQ</a></li>
                    </ul>
                </div>

                <div>
                    <p class="text-sm font-semibold text-primary-900 dark:text-white">Mulai</p>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li><a href="{{ route('login') }}" class="text-slate-500 transition hover:text-primary-700 dark:text-slate-400 dark:hover:text-primary-200">Masuk ke aplikasi</a></li>
                        <li><span class="text-slate-500 dark:text-slate-400">Akun demo tersedia di halaman masuk</span></li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-3 border-t border-primary-900/[0.06] pt-6 text-xs text-slate-400 dark:border-white/10 dark:text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} SIMAS. Proyek portofolio — seluruh data adalah contoh non-sensitif.</p>
                <p class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5"><x-icon name="bolt" class="h-3.5 w-3.5 text-gold-500" /> Laravel 10</span>
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
