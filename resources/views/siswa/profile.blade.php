<x-layouts.app :title="'Profil Saya'">
    @if (! $student)
        <div class="card-pad">
            <x-empty-state icon="user-circle"
                           title="Profil belum tersedia"
                           description="Akun Anda belum ditautkan ke profil siswa. Hubungi admin untuk melengkapinya." />
        </div>
    @else
        <div class="mb-5">
            <p class="page-subtitle">
                Data profil dan kelas Anda. Perubahan data hanya dapat dilakukan oleh admin.
            </p>
        </div>

        <div class="grid gap-5 lg:grid-cols-3">
            {{-- Kartu utama --}}
            <div class="card-pad lg:col-span-2">
                <div class="flex items-start gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-primary-500 text-lg font-bold text-white">
                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 2)) }}
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-bold text-primary-900 dark:text-white">{{ $user->name }}</h2>
                            <span class="badge-neutral">{{ $user->roleLabel() }}</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            NIS {{ $student->student_number }} · {{ '@'.$user->username }}
                        </p>
                    </div>
                </div>

                <dl class="mt-6 grid gap-x-6 gap-y-4 border-t border-primary-900/[0.08] pt-5 dark:border-white/10 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kelas</dt>
                        <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                            {{ $student->schoolClass?->displayName() ?? 'Belum ditempatkan' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Wali Kelas</dt>
                        <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                            {{ $student->schoolClass?->homeroomTeacher?->user?->name ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Email</dt>
                        <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Telepon</dt>
                        <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $student->phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tanggal Lahir</dt>
                        <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                            {{ $student->birth_date ? $student->birth_date->isoFormat('D MMMM Y') : '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Status Akun</dt>
                        <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Alamat</dt>
                        <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $student->address ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Ringkasan --}}
            <div class="space-y-5">
                <div class="card-pad">
                    <h2 class="section-title">Ringkasan Kehadiran</h2>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        Persentase dihitung dari jumlah hadir dibagi total catatan absensi yang tercatat.
                    </p>

                    <div class="mt-4 space-y-2">
                        @php $summary = $student->attendanceSummary(); @endphp

                        @foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'] as $key => $label)
                            <div class="flex items-center justify-between rounded-xl border border-primary-900/[0.08] px-3.5 py-2 dark:border-white/10">
                                <span class="text-sm text-slate-600 dark:text-slate-300">{{ $label }}</span>
                                <span class="font-bold tabular-nums text-primary-800 dark:text-primary-200">{{ $summary[$key] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 flex items-center justify-between rounded-xl bg-primary-50 px-3.5 py-3 dark:bg-primary-500/10">
                        <span class="text-sm font-medium text-primary-700 dark:text-primary-200">Persentase</span>
                        @php $percentage = $student->attendancePercentage(); @endphp
                        @if ($percentage !== null)
                            <span class="text-xl font-bold text-primary-800 dark:text-primary-100">{{ number_format($percentage, 1, ',', '.') }}%</span>
                        @else
                            <span class="text-sm font-medium text-slate-400">Belum ada data</span>
                        @endif
                    </div>
                </div>

                <div class="card-pad">
                    <h2 class="section-title">Akses Anda</h2>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        Akun siswa hanya dapat melihat jadwal kelas, profil, dan riwayat absensi sendiri —
                        termasuk ringkasan kehadiran pribadi.
                    </p>
                </div>
            </div>
        </div>
    @endif
</x-layouts.app>
