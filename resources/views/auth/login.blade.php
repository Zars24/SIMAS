<x-layouts.auth title="Masuk">
    <h1 class="text-2xl font-bold tracking-tight text-primary-900 dark:text-white">Selamat datang kembali 👋</h1>
    <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">
        Masuk untuk mengelola data akademik dan absensi sekolah.
    </p>

    <div class="mt-8">
        @if (session('status'))
            <div class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3.5 text-sm text-emerald-800 dark:border-emerald-400/30 dark:bg-emerald-500/10 dark:text-emerald-200" role="status">
                <x-icon name="check-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ showPassword: false, loading: false }" @submit="loading = true">
            @csrf

            <div>
                <label for="login" class="form-label">Email atau Username <span class="req" aria-hidden="true">*</span></label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-icon name="user" class="h-5 w-5" />
                    </span>
                    <input id="login" name="login" type="text" value="{{ old('login') }}"
                           class="form-input pl-10 @error('login') is-invalid @enderror"
                           placeholder="nama@simas.test" required autofocus autocomplete="username"
                           @error('login') aria-invalid="true" aria-describedby="login-error" @enderror>
                </div>
                @error('login')
                    <p class="form-error" id="login-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="form-label">Kata Sandi <span class="req" aria-hidden="true">*</span></label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-icon name="lock-closed" class="h-5 w-5" />
                    </span>
                    <input id="password" name="password" :type="showPassword ? 'text' : 'password'"
                           class="form-input pl-10 pr-11 @error('password') is-invalid @enderror"
                           placeholder="••••••••" required autocomplete="current-password"
                           @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>

                    <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 transition hover:text-primary-600"
                            :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                            :aria-pressed="showPassword">
                        <x-icon name="eye" class="h-5 w-5" x-show="!showPassword" />
                        <x-icon name="eye-slash" class="h-5 w-5" x-cloak x-show="showPassword" />
                    </button>
                </div>
                @error('password')
                    <p class="form-error" id="password-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <label for="remember" class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-600 dark:text-slate-300">
                    <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))
                           class="form-checkbox h-4 w-4">
                    Ingat saya
                </label>
            </div>

            <button type="submit" class="btn-primary w-full py-3" :disabled="loading">
                <svg x-cloak x-show="loading" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                </svg>
                <span x-text="loading ? 'Memproses…' : 'Masuk'">Masuk</span>
            </button>
        </form>
    </div>

    {{-- Akun demo --}}
    <div class="mt-8 rounded-2xl border border-dashed border-primary-300 bg-primary-50/60 p-4 dark:border-primary-700 dark:bg-primary-900/30" x-data="{ open: true }">
        <button type="button" @click="open = !open" class="flex w-full items-center justify-between gap-2 text-left" :aria-expanded="open">
            <span class="flex items-center gap-2 text-sm font-semibold text-primary-800 dark:text-primary-100">
                <x-icon name="sparkles" class="h-4 w-4 text-gold-500" />
                Akun demo untuk mencoba
            </span>
            <x-icon name="chevron-down" class="h-4 w-4 text-primary-500 transition" ::class="open ? 'rotate-180' : ''" />
        </button>

        <dl x-show="open" x-collapse.duration.200ms class="mt-3 space-y-2 text-sm">
            @foreach ([
                ['Administrator', 'admin@simas.test'],
                ['Guru', 'budi.santoso@simas.test'],
                ['Siswa', 'andi.pratama@siswa.simas.test'],
            ] as [$role, $email])
                <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1 rounded-xl bg-white/70 px-3 py-2 dark:bg-primary-950/40">
                    <dt class="font-medium text-slate-600 dark:text-slate-300">{{ $role }}</dt>
                    <dd class="font-mono text-xs text-primary-800 dark:text-primary-200">{{ $email }}</dd>
                </div>
            @endforeach
            <p class="pt-1 text-xs text-slate-500 dark:text-slate-400">
                Kata sandi semua akun demo: <code class="kbd">password</code>
            </p>
        </dl>
    </div>

    <p class="mt-8 text-center text-sm text-slate-500 dark:text-slate-400">
        <a href="{{ route('landing') }}" class="inline-flex items-center gap-1.5 font-medium text-primary-600 transition hover:text-primary-700 dark:text-primary-300">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke halaman utama
        </a>
    </p>
</x-layouts.auth>
