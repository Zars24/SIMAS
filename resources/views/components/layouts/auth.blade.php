@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>

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
    <div class="grid min-h-full lg:grid-cols-2">
        {{-- Panel identitas --}}
        <aside class="relative hidden overflow-hidden bg-gradient-to-br from-primary-800 via-primary-900 to-primary-950 lg:flex lg:flex-col lg:justify-between lg:p-12">
            {{-- Ornamen --}}
            <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-gold-400/10 blur-2xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-primary-400/10 blur-2xl" aria-hidden="true"></div>

            <div class="relative">
                <a href="{{ route('landing') }}" class="inline-flex items-center gap-3 rounded-xl text-white">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gold-400 text-primary-950 shadow-sm">
                        <x-icon name="academic-cap" class="h-6 w-6" />
                    </span>
                    <span class="text-2xl font-bold tracking-tight">SIMAS</span>
                </a>

                <p class="mt-3 max-w-md text-sm text-primary-100/70">
                    Sistem Informasi Manajemen Absensi Siswa — {{ config('simas.school_name') }}
                </p>
            </div>

            <div class="relative max-w-lg">
                <h2 class="text-balance text-3xl font-bold leading-tight text-white">
                    Absensi Lebih Mudah, Pengelolaan Sekolah Lebih Teratur.
                </h2>

                <p class="mt-4 text-primary-100/80">
                    Catat kehadiran kelas dalam hitungan detik, pantau rekap otomatis,
                    dan beri siswa akses aman ke riwayat mereka sendiri.
                </p>

                <ul class="mt-8 space-y-3 text-sm text-primary-100/90">
                    <li class="flex items-center gap-3">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10">
                            <x-icon name="check" class="h-3.5 w-3.5 text-gold-300" />
                        </span>
                        Pencatatan massal per kelas dengan validasi anti-duplikasi
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10">
                            <x-icon name="check" class="h-3.5 w-3.5 text-gold-300" />
                        </span>
                        Hak akses tegas: Admin, Guru, dan Siswa
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10">
                            <x-icon name="check" class="h-3.5 w-3.5 text-gold-300" />
                        </span>
                        Rekap, grafik tren, dan ekspor CSV siap laporan
                    </li>
                </ul>
            </div>

            <p class="relative text-xs text-primary-200/50">
                &copy; {{ date('Y') }} SIMAS. Proyek portofolio — data bersifat demo.
            </p>
        </aside>

        {{-- Panel formulir --}}
        <main class="flex min-h-full items-center justify-center px-4 py-10 sm:px-8">
            <div class="w-full max-w-md animate-fade-up">
                {{-- Brand mobile --}}
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-primary-500 text-white">
                        <x-icon name="academic-cap" class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="text-lg font-bold tracking-tight text-primary-900 dark:text-white">SIMAS</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ config('simas.school_name') }}</p>
                    </div>
                </div>

                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
