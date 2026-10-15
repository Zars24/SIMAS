@props(['title' => null])

@php
    $user = auth()->user();

    // Ringkasan aktivitas singkat buat indikator notifikasi.
    $notifications = [];

    if ($user) {
        $today = now()->toDateString();
        $todayKey = strtolower(now()->format('l'));

        if ($user->isGuru() && $user->teacher) {
            $pending = \App\Models\Schedule::query()
                ->forTeacher($user->teacher->id)
                ->active()
                ->where('day_of_week', $todayKey)
                ->whereDoesntHave('attendanceSessions', fn ($query) => $query->whereDate('session_date', $today))
                ->count();

            if ($pending > 0) {
                $notifications[] = [
                    'icon' => 'clipboard-document-check',
                    'text' => $pending.' sesi jadwal hari ini belum diisi absensinya.',
                    'url' => route('guru.attendance.index'),
                ];
            }
        }

        if ($user->isAdmin()) {
            $pending = \App\Models\Schedule::query()
                ->active()
                ->where('day_of_week', $todayKey)
                ->whereDoesntHave('attendanceSessions', fn ($query) => $query->whereDate('session_date', $today))
                ->count();

            if ($pending > 0) {
                $notifications[] = [
                    'icon' => 'clipboard-document-check',
                    'text' => $pending.' sesi jadwal hari ini belum diisi absensinya.',
                    'url' => route('admin.attendance.index'),
                ];
            }
        }
    }

    $initials = $user
        ? \Illuminate\Support\Collection::make(explode(' ', $user->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('')
        : '?';

    $groups = [];
    if ($user?->isAdmin()) {
        $groups = [
            'Utama' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'squares-2x2', 'pattern' => 'admin.dashboard'],
            ],
            'Akademik' => [
                ['label' => 'Guru', 'route' => 'admin.teachers.index', 'icon' => 'briefcase', 'pattern' => 'admin.teachers.*'],
                ['label' => 'Siswa', 'route' => 'admin.students.index', 'icon' => 'academic-cap', 'pattern' => 'admin.students.*'],
                ['label' => 'Kelas', 'route' => 'admin.classes.index', 'icon' => 'building-library', 'pattern' => 'admin.classes.*'],
                ['label' => 'Mata Pelajaran', 'route' => 'admin.subjects.index', 'icon' => 'book-open', 'pattern' => 'admin.subjects.*'],
                ['label' => 'Jadwal', 'route' => 'admin.schedules.index', 'icon' => 'calendar-days', 'pattern' => 'admin.schedules.*'],
            ],
            'Absensi & Laporan' => [
                ['label' => 'Rekap Absensi', 'route' => 'admin.attendance.index', 'icon' => 'table-cells', 'pattern' => 'admin.attendance.*'],
            ],
            'Sistem' => [
                ['label' => 'Pengguna', 'route' => 'admin.users.index', 'icon' => 'users', 'pattern' => 'admin.users.*'],
                ['label' => 'Audit Log', 'route' => 'admin.audit-logs.index', 'icon' => 'document-text', 'pattern' => 'admin.audit-logs.*'],
            ],
        ];
    } elseif ($user?->isGuru()) {
        $groups = [
            'Utama' => [
                ['label' => 'Dashboard', 'route' => 'guru.dashboard', 'icon' => 'squares-2x2', 'pattern' => 'guru.dashboard'],
                ['label' => 'Jadwal Saya', 'route' => 'guru.schedules.index', 'icon' => 'calendar-days', 'pattern' => 'guru.schedules.*'],
            ],
            'Absensi' => [
                ['label' => 'Isi & Riwayat Absensi', 'route' => 'guru.attendance.index', 'icon' => 'clipboard-document-check', 'pattern' => 'guru.attendance.*'],
            ],
        ];
    } elseif ($user?->isSiswa()) {
        $groups = [
            'Utama' => [
                ['label' => 'Dashboard', 'route' => 'siswa.dashboard', 'icon' => 'squares-2x2', 'pattern' => 'siswa.dashboard'],
            ],
            'Data Saya' => [
                ['label' => 'Jadwal Kelas', 'route' => 'siswa.schedule', 'icon' => 'calendar-days', 'pattern' => 'siswa.schedule'],
                ['label' => 'Riwayat Absensi', 'route' => 'siswa.attendance', 'icon' => 'clipboard-document-list', 'pattern' => 'siswa.attendance'],
                ['label' => 'Profil', 'route' => 'siswa.profile', 'icon' => 'user-circle', 'pattern' => 'siswa.profile'],
            ],
        ];
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    <div class="min-h-full">
        {{-- Backdrop drawer mobile --}}
        <div x-cloak x-show="$store.sidebar.mobileOpen" x-transition.opacity.duration.200ms
             class="fixed inset-0 z-30 bg-primary-950/60 backdrop-blur-[2px] lg:hidden"
             @click="$store.sidebar.closeMobile()" aria-hidden="true"></div>

        {{-- Sidebar --}}
        <aside x-data
               :class="[
                   $store.sidebar.mobileOpen ? 'translate-x-0' : '-translate-x-full',
                   $store.sidebar.collapsed ? 'lg:w-20' : 'lg:w-72',
               ]"
               class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col bg-gradient-to-b from-primary-900 via-primary-900 to-primary-950 text-white shadow-pop transition-all duration-200 ease-out-soft lg:translate-x-0"
               aria-label="Navigasi utama">
            {{-- Brand --}}
            <div class="flex h-16 shrink-0 items-center gap-2.5 px-5">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 rounded-xl focus-visible:ring-gold-400">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gold-400 text-primary-950 shadow-sm">
                        <x-icon name="academic-cap" class="h-5 w-5" />
                    </span>
                    <span :class="$store.sidebar.collapsed ? 'lg:hidden' : ''" class="text-lg font-bold tracking-tight">SIMAS</span>
                </a>

                <button type="button" @click="$store.sidebar.closeMobile()"
                        class="ml-auto rounded-lg p-1.5 text-primary-100/70 transition hover:bg-white/10 hover:text-white lg:hidden"
                        aria-label="Tutup menu">
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            {{-- Menu --}}
            <nav class="flex-1 space-y-1 overflow-y-auto px-3 pb-4">
                @foreach ($groups as $groupLabel => $items)
                    <p class="nav-group-label" :class="$store.sidebar.collapsed ? 'lg:hidden' : ''">{{ $groupLabel }}</p>

                    @foreach ($items as $item)
                        @php $active = request()->routeIs($item['pattern']); @endphp
                        <a href="{{ route($item['route']) }}"
                           class="nav-link {{ $active ? 'nav-link-active' : '' }}"
                           @if ($active) aria-current="page" @endif
                           @if ($item['label']) title="{{ $item['label'] }}" @endif>
                            <x-icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                            <span :class="$store.sidebar.collapsed ? 'lg:hidden' : ''" class="truncate">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                @endforeach
            </nav>

            {{-- Pengguna --}}
            <div class="shrink-0 border-t border-white/10 p-3">
                <div class="flex items-center gap-3 rounded-xl bg-white/5 p-2.5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gold-400 text-sm font-bold text-primary-950">
                        {{ $initials }}
                    </span>

                    <div class="min-w-0 flex-1" :class="$store.sidebar.collapsed ? 'lg:hidden' : ''">
                        <p class="truncate text-sm font-semibold">{{ $user?->name }}</p>
                        <p class="truncate text-xs text-primary-200/70">{{ $user?->roleLabel() }}</p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="shrink-0" :class="$store.sidebar.collapsed ? 'lg:hidden' : ''">
                        @csrf
                        <button type="submit"
                                class="rounded-lg p-1.5 text-primary-100/70 transition hover:bg-white/10 hover:text-white"
                                title="Keluar" aria-label="Keluar">
                            <x-icon name="arrow-right-on-rectangle" class="h-5 w-5" />
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Konten --}}
        <div class="flex min-h-full flex-col transition-all duration-200 ease-out-soft"
             :class="$store.sidebar.collapsed ? 'lg:pl-20' : 'lg:pl-72'">
            {{-- Header --}}
            <header class="sticky top-0 z-20 border-b border-primary-900/[0.08] bg-cream/85 backdrop-blur dark:border-white/10 dark:bg-primary-950/85">
                <div class="mx-auto flex h-16 w-full max-w-7xl items-center gap-2 px-4 sm:px-6 lg:px-8">
                    <button type="button" @click="$store.sidebar.openMobile()"
                            class="-ml-2 rounded-xl p-2 text-primary-700 transition hover:bg-primary-100 dark:text-primary-200 dark:hover:bg-primary-900/60 lg:hidden"
                            aria-label="Buka menu">
                        <x-icon name="bars-3" class="h-5 w-5" />
                    </button>

                    <button type="button" @click="$store.sidebar.toggleCollapse()"
                            class="-ml-2 hidden rounded-xl p-2 text-primary-700 transition hover:bg-primary-100 dark:text-primary-200 dark:hover:bg-primary-900/60 lg:inline-flex"
                            aria-label="Ciutkan atau lebarkan sidebar">
                        <x-icon name="bars-3" class="h-5 w-5" />
                    </button>

                    <h1 class="min-w-0 flex-1 truncate text-lg font-bold tracking-tight text-primary-900 dark:text-white">
                        {{ $title ?? 'Dashboard' }}
                    </h1>

                    {{-- Tema --}}
                    <button type="button" @click="$store.theme.toggle()"
                            class="rounded-xl p-2 text-primary-700 transition hover:bg-primary-100 dark:text-primary-200 dark:hover:bg-primary-900/60"
                            aria-label="Ganti tema terang atau gelap" title="Ganti tema">
                        <x-icon name="sun" class="hidden h-5 w-5 dark:block" />
                        <x-icon name="moon" class="h-5 w-5 dark:hidden" />
                    </button>

                    {{-- Notifikasi --}}
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                        <button type="button" @click="open = !open"
                                class="relative rounded-xl p-2 text-primary-700 transition hover:bg-primary-100 dark:text-primary-200 dark:hover:bg-primary-900/60"
                                aria-haspopup="true" :aria-expanded="open" aria-label="Notifikasi" title="Notifikasi">
                            <x-icon name="bell" class="h-5 w-5" />
                            @if (count($notifications) > 0)
                                <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-gold-400 ring-2 ring-cream dark:ring-primary-950" aria-hidden="true"></span>
                            @endif
                        </button>

                        <div x-cloak x-show="open" x-transition.origin.top.right.duration.150ms
                             class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] rounded-2xl border border-primary-900/10 bg-white p-2 shadow-pop dark:border-white/10 dark:bg-primary-900"
                             role="menu">
                            <p class="px-3 pb-1 pt-2 text-xs font-bold uppercase tracking-widest text-primary-400 dark:text-primary-300/70">
                                Notifikasi
                            </p>

                            @forelse ($notifications as $notification)
                                <a href="{{ $notification['url'] }}" role="menuitem"
                                   class="flex items-start gap-3 rounded-xl px-3 py-2.5 transition hover:bg-primary-50 dark:hover:bg-white/5">
                                    <x-icon :name="$notification['icon']" class="mt-0.5 h-5 w-5 shrink-0 text-gold-500" />
                                    <span class="text-sm text-slate-700 dark:text-slate-200">{{ $notification['text'] }}</span>
                                </a>
                            @empty
                                <div class="flex items-start gap-3 rounded-xl px-3 py-2.5">
                                    <x-icon name="check-circle" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" />
                                    <span class="text-sm text-slate-500 dark:text-slate-400">Tidak ada notifikasi. Semua berjalan lancar.</span>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Profil --}}
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                        <button type="button" @click="open = !open"
                                class="flex items-center gap-2 rounded-xl p-1.5 pr-2 transition hover:bg-primary-100 dark:hover:bg-primary-900/60"
                                aria-haspopup="true" :aria-expanded="open">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-500 text-xs font-bold text-white">
                                {{ $initials }}
                            </span>
                            <span class="hidden text-left sm:block">
                                <span class="block max-w-[10rem] truncate text-sm font-semibold text-primary-900 dark:text-white">{{ $user?->name }}</span>
                                <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $user?->roleLabel() }}</span>
                            </span>
                            <x-icon name="chevron-down" class="hidden h-4 w-4 text-slate-400 sm:block" />
                        </button>

                        <div x-cloak x-show="open" x-transition.origin.top.right.duration.150ms
                             class="absolute right-0 mt-2 w-64 rounded-2xl border border-primary-900/10 bg-white p-2 shadow-pop dark:border-white/10 dark:bg-primary-900"
                             role="menu">
                            <div class="border-b border-primary-900/[0.08] px-3 pb-3 pt-2 dark:border-white/10">
                                <p class="truncate text-sm font-semibold text-primary-900 dark:text-white">{{ $user?->name }}</p>
                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $user?->email }}</p>
                                <span class="badge-neutral mt-2">{{ $user?->roleLabel() }}</span>
                            </div>

                            @if ($user?->isSiswa())
                                <a href="{{ route('siswa.profile') }}" role="menuitem"
                                   class="mt-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-primary-50 dark:text-slate-200 dark:hover:bg-white/5">
                                    <x-icon name="user-circle" class="h-5 w-5 text-primary-500" />
                                    Profil saya
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" role="menuitem"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-rose-600 transition hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10">
                                    <x-icon name="arrow-right-on-rectangle" class="h-5 w-5" />
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Isi halaman --}}
            <main id="main" class="mx-auto w-full max-w-7xl flex-1 animate-fade-in px-4 py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>

            <footer class="mx-auto w-full max-w-7xl px-4 pb-6 pt-2 sm:px-6 lg:px-8">
                <p class="text-xs text-slate-400 dark:text-slate-500">
                    &copy; {{ date('Y') }} SIMAS — {{ config('simas.school_name') }}. Data demo untuk keperluan portofolio.
                </p>
            </footer>
        </div>
    </div>

    {{-- Toast --}}
    <div class="pointer-events-none fixed inset-x-0 bottom-0 z-50 flex flex-col items-end gap-2 p-4 sm:p-6" aria-live="polite" aria-atomic="true">
        @if (session('success'))
            <div x-data="toastItem()" x-show="visible" x-transition.opacity.duration.200ms
                 class="toast-success animate-slide-in-right" role="status">
                <x-icon name="check-circle" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" />
                <p class="flex-1 text-sm font-medium">{{ session('success') }}</p>
                <button type="button" @click="dismiss()" class="rounded-lg p-1 transition hover:bg-black/5" aria-label="Tutup notifikasi">
                    <x-icon name="x-mark" class="h-4 w-4" />
                </button>
            </div>
        @endif

        @if (session('error'))
            <div x-data="toastItem()" x-show="visible" x-transition.opacity.duration.200ms
                 class="toast-error animate-slide-in-right" role="alert">
                <x-icon name="exclamation-circle" class="mt-0.5 h-5 w-5 shrink-0 text-rose-600 dark:text-rose-400" />
                <p class="flex-1 text-sm font-medium">{{ session('error') }}</p>
                <button type="button" @click="dismiss()" class="rounded-lg p-1 transition hover:bg-black/5" aria-label="Tutup notifikasi">
                    <x-icon name="x-mark" class="h-4 w-4" />
                </button>
            </div>
        @endif

        @if (session('status'))
            <div x-data="toastItem()" x-show="visible" x-transition.opacity.duration.200ms
                 class="toast-info animate-slide-in-right" role="status">
                <x-icon name="information-circle" class="mt-0.5 h-5 w-5 shrink-0 text-primary-600 dark:text-primary-300" />
                <p class="flex-1 text-sm font-medium">{{ session('status') }}</p>
                <button type="button" @click="dismiss()" class="rounded-lg p-1 transition hover:bg-black/5" aria-label="Tutup notifikasi">
                    <x-icon name="x-mark" class="h-4 w-4" />
                </button>
            </div>
        @endif

        @if ($errors->any() && ! session('error'))
            <div x-data="toastItem(8000)" x-show="visible" x-transition.opacity.duration.200ms
                 class="toast-error animate-slide-in-right" role="alert">
                <x-icon name="exclamation-circle" class="mt-0.5 h-5 w-5 shrink-0 text-rose-600 dark:text-rose-400" />
                <div class="flex-1">
                    <p class="text-sm font-semibold">
                        {{ $errors->count() > 1 ? $errors->count().' kesalahan pada formulir.' : 'Periksa kembali input Anda.' }}
                    </p>
                    <p class="mt-0.5 text-sm">{{ $errors->first() }}</p>
                </div>
                <button type="button" @click="dismiss()" class="rounded-lg p-1 transition hover:bg-black/5" aria-label="Tutup notifikasi">
                    <x-icon name="x-mark" class="h-4 w-4" />
                </button>
            </div>
        @endif
    </div>

    {{-- Dialog konfirmasi global (form[data-confirm]) --}}
    <div x-data="confirmDialog" @confirm:open.window="show($event.detail)" @keydown.escape.window="if (open) cancel()">
        <div x-cloak x-show="open">
            <div x-show="open" x-transition.opacity.duration.150ms class="modal-backdrop" @click="cancel()" aria-hidden="true"></div>

            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div x-show="open" x-transition.duration.150ms
                     class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
                    <div class="flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300">
                            <x-icon name="exclamation-triangle" class="h-6 w-6" />
                        </span>

                        <div class="min-w-0">
                            <h2 id="confirm-title" class="modal-title" x-text="title"></h2>
                            <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400" x-text="message"></p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" class="btn-outline" @click="cancel()">Batal</button>
                        <button type="button" class="btn-danger" x-ref="confirmButton" @click="confirm()" x-text="confirmLabel"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
