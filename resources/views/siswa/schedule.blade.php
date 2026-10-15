<x-layouts.app :title="'Jadwal Kelas'">
    <div class="mb-5">
        <p class="page-subtitle">
            Jadwal pelajaran kelas Anda pada tahun ajaran aktif.
        </p>
    </div>

    @if (! $student)
        <div class="card-pad">
            <x-empty-state icon="user-circle"
                           title="Profil belum tersedia"
                           description="Akun Anda belum ditautkan ke profil siswa." />
        </div>
    @elseif (! $student->schoolClass)
        <div class="card-pad">
            <x-empty-state icon="building-library"
                           title="Belum ditempatkan di kelas"
                           description="Anda belum ditempatkan pada kelas mana pun. Hubungi admin untuk penempatan kelas." />
        </div>
    @elseif ($schedules->isEmpty())
        <div class="card-pad">
            <x-empty-state icon="calendar-days"
                           title="Jadwal belum tersedia"
                           description="Kelas {{ $student->schoolClass->displayName() }} belum memiliki jadwal pelajaran." />
        </div>
    @else
        <div class="card-pad mb-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-primary-900 dark:text-white">{{ $student->schoolClass->name }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Wali kelas: {{ $student->schoolClass->homeroomTeacher?->user?->name ?? '—' }}
                    </p>
                </div>
                <span class="badge-neutral">Tahun ajaran {{ $student->schoolClass->academic_year }}</span>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach (\App\Models\Schedule::DAY_ORDER as $dayKey)
                @continue (! $schedules->has($dayKey))

                <div class="card">
                    <div class="flex items-center justify-between gap-2 border-b border-primary-900/[0.08] px-5 py-3.5 dark:border-white/10">
                        <h2 class="section-title">{{ \App\Models\Schedule::DAYS[$dayKey] }}</h2>
                        <span class="badge-neutral">{{ $schedules[$dayKey]->count() }} sesi</span>
                    </div>

                    <ul class="divide-y divide-primary-900/[0.06] dark:divide-white/[0.06]">
                        @foreach ($schedules[$dayKey] as $schedule)
                            <li class="flex items-start justify-between gap-3 px-5 py-3.5">
                                <div class="min-w-0">
                                    <p class="font-medium text-primary-900 dark:text-white">{{ $schedule->subject?->name }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $schedule->teacher?->user?->name ?? '—' }}
                                    </p>
                                </div>
                                <span class="shrink-0 tabular-nums text-xs font-medium text-slate-500 dark:text-slate-400">
                                    {{ $schedule->timeRange() }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
