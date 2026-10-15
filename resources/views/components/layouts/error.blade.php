@props([
    'title' => null,
    'code' => '404',
    'heading' => 'Terjadi kesalahan',
    'description' => null,
    'icon' => 'exclamation-triangle',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $code }} · {{ config('app.name') }}</title>

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
    <div class="relative flex min-h-full flex-col items-center justify-center overflow-hidden px-4 py-12 sm:px-8">
        {{-- Ornamen latar --}}
        <div class="pointer-events-none absolute -right-32 -top-32 h-96 w-96 rounded-full bg-primary-400/10 blur-2xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-40 -left-24 h-96 w-96 rounded-full bg-gold-400/10 blur-2xl" aria-hidden="true"></div>

        <div class="relative w-full max-w-xl text-center animate-fade-up">
            {{-- Brand --}}
            <a href="{{ route('landing') }}" class="inline-flex items-center gap-3 rounded-xl">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary-500 text-white shadow-sm">
                    <x-icon name="academic-cap" class="h-6 w-6" />
                </span>
                <span class="text-xl font-bold tracking-tight text-primary-900 dark:text-white">SIMAS</span>
            </a>

            {{-- Kartu error --}}
            <div class="card-pad mt-8">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-100 text-primary-600 dark:bg-primary-500/15 dark:text-primary-300">
                    <x-icon :name="$icon" class="h-7 w-7" />
                </div>

                <p class="mt-4 text-5xl font-bold tracking-tight text-primary-900 dark:text-white">{{ $code }}</p>
                <h1 class="mt-2 text-lg font-bold text-primary-900 dark:text-white">{{ $heading }}</h1>

                @if ($description)
                    <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                        {{ $description }}
                    </p>
                @endif

                <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary">
                            <x-icon name="squares-2x2" class="h-4 w-4" />
                            Ke Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-primary">
                            <x-icon name="arrow-right-on-rectangle" class="h-4 w-4" />
                            Masuk
                        </a>
                    @endauth

                    <a href="{{ route('landing') }}" class="btn-outline">
                        <x-icon name="home" class="h-4 w-4" />
                        Beranda
                    </a>
                </div>
            </div>

            <p class="mt-6 text-xs text-slate-400 dark:text-slate-500">
                &copy; {{ date('Y') }} SIMAS — {{ config('simas.school_name') }}. Data demo untuk keperluan portofolio.
            </p>
        </div>
    </div>
</body>
</html>
