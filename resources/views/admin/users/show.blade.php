<x-layouts.app :title="'Detail Akun: '.$user->name">
    <div class="mb-5">
        <a href="{{ route('admin.users.index') }}" class="btn-ghost btn-sm -ml-2">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke daftar
        </a>
        <p class="page-subtitle mt-2">Informasi akun, profil terkait, dan tindakan administratif.</p>
    </div>

    <div class="grid gap-5 lg:grid-cols-3">
        {{-- Kartu profil --}}
        <div class="card-pad lg:col-span-2">
            <div class="flex items-start gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-primary-500 text-lg font-bold text-white">
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 2)) }}
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-lg font-bold text-primary-900 dark:text-white">{{ $user->name }}</h2>
                        <span class="{{ $user->isAdmin() ? 'badge-gold' : 'badge-neutral' }}">{{ $user->roleLabel() }}</span>
                        @if ($user->isPrimaryAdmin())
                            <span class="badge-emerald">Admin Utama</span>
                        @endif
                        @unless ($user->is_active)
                            <span class="badge-alpa">Nonaktif</span>
                        @endunless
                    </div>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ '@'.$user->username }} · {{ $user->email }}</p>
                </div>
            </div>

            <dl class="mt-6 grid gap-x-6 gap-y-4 border-t border-primary-900/[0.08] pt-5 dark:border-white/10 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Dibuat</dt>
                    <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">@tanggaljam($user->created_at)</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Terakhir diperbarui</dt>
                    <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">@tanggaljam($user->updated_at)</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Email terverifikasi</dt>
                    <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $user->email_verified_at ? 'Ya' : 'Belum (tidak wajib untuk demo)' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Status</dt>
                    <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $user->is_active ? 'Aktif' : 'Nonaktif — login ditolak' }}</dd>
                </div>

                @if ($user->teacher)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Profil Guru</dt>
                        <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                            NIP: {{ $user->teacher->teacher_number ?? '—' }}
                            @if ($user->teacher->homeroomClasses->isNotEmpty())
                                · Wali kelas:
                                @foreach ($user->teacher->homeroomClasses as $class)
                                    <span class="badge-neutral">{{ $class->displayName() }}</span>
                                @endforeach
                            @endif
                            · <a href="{{ route('admin.teachers.edit', $user->teacher) }}" class="font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-300">Ubah profil guru</a>
                        </dd>
                    </div>
                @endif

                @if ($user->student)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Profil Siswa</dt>
                        <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                            NIS: {{ $user->student->student_number }}
                            · Kelas: {{ $user->student->schoolClass?->displayName() ?? 'Belum ditempatkan' }}
                            · <a href="{{ route('admin.students.show', $user->student) }}" class="font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-300">Lihat profil siswa</a>
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="space-y-5">
            {{-- Reset kata sandi --}}
            <div class="card-pad">
                <h2 class="section-title">Reset Kata Sandi</h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Tetapkan kata sandi baru untuk pengguna ini. Sampaikan kata sandi secara aman dan minta segera diganti.
                </p>

                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="mt-4"
                      data-confirm="Reset kata sandi {{ $user->name }}?" data-confirm-title="Reset kata sandi" data-confirm-label="Ya, reset">
                    @csrf
                    @method('PUT')

                    <label for="password" class="form-label">Kata Sandi Baru <span class="req" aria-hidden="true">*</span></label>
                    <input id="password" name="password" type="text" required minlength="8"
                           class="form-input @error('password') is-invalid @enderror" placeholder="Minimal 8 karakter">
                    @error('password') <p class="form-error">{{ $message }}</p> @enderror

                    <button type="submit" class="btn-outline mt-3 w-full">
                        <x-icon name="arrow-path" class="h-4 w-4" />
                        Reset Kata Sandi
                    </button>
                </form>
            </div>

            {{-- Tindakan --}}
            <div class="card-pad">
                <h2 class="section-title">Tindakan</h2>

                <div class="mt-4 flex flex-col gap-2">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn-outline w-full">
                        <x-icon name="pencil-square" class="h-4 w-4" />
                        Ubah Akun
                    </a>

                    <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}"
                          data-confirm="{{ $user->is_active ? 'Nonaktifkan akun '.$user->name.'?' : 'Aktifkan kembali akun '.$user->name.'?' }}"
                          data-confirm-title="{{ $user->is_active ? 'Nonaktifkan akun' : 'Aktifkan akun' }}"
                          data-confirm-label="{{ $user->is_active ? 'Ya, nonaktifkan' : 'Ya, aktifkan' }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-outline w-full">
                            <x-icon name="arrow-path" class="h-4 w-4" />
                            {{ $user->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                          data-confirm="Hapus akun {{ $user->name }}? Tindakan ini tidak dapat dibatalkan."
                          data-confirm-title="Hapus akun">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger w-full">
                            <x-icon name="trash" class="h-4 w-4" />
                            Hapus Akun
                        </button>
                    </form>
                </div>

                <p class="mt-4 text-xs leading-relaxed text-slate-400 dark:text-slate-500">
                    Akun yang memiliki riwayat absensi tidak dapat dihapus — nonaktifkan sebagai gantinya agar riwayat tetap utuh.
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>
