<x-layouts.app :title="'Jadwal Saya'">
    <div class="mb-5">
        <p class="page-subtitle">
            Seluruh jadwal mengajar yang ditugaskan kepada Anda. Absensi hanya dapat diisi pada jadwal di bawah ini.
        </p>
    </div>

    @if (! $teacher)
        <div class="card-pad">
            <x-empty-state icon="briefcase"
                           title="Profil guru belum tersedia"
                           description="Akun Anda belum ditautkan ke profil guru. Hubungi admin untuk menugaskan jadwal mengajar." />
        </div>
    @elseif ($schedules->isEmpty())
        <div class="card-pad">
            <x-empty-state icon="calendar-days"
                           title="Belum ada jadwal mengajar"
                           description="Anda belum ditugaskan pada jadwal apa pun. Hubungi admin untuk pengaturan jadwal." />
        </div>
    @else
        @php
            $totalSessions = $schedules->flatten()->sum('attendance_sessions_count');
            $totalSlots = $schedules->flatten()->count();
        @endphp

        <div class="mb-5 grid gap-4 sm:grid-cols-3">
            <x-stat-card label="Total Sesi Jadwal" :value="$totalSlots" icon="calendar-days" tone="primary" animate />
            <x-stat-card label="Hari Mengajar" :value="$schedules->count()" icon="clock" tone="emerald" animate />
            <x-stat-card label="Absensi Tercatat" :value="$totalSessions" icon="clipboard-document-check" tone="gold" animate />
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
                            <li class="px-5 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-primary-900 dark:text-white">{{ $schedule->subject?->name }}</p>
                                        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                                            {{ $schedule->schoolClass?->name }} · {{ $schedule->timeRange() }}
                                        </p>
                                    </div>

                                    @if (! $schedule->is_active)
                                        <span class="badge-slate shrink-0">Nonaktif</span>
                                    @endif
                                </div>

                                <div class="mt-3 flex items-center justify-between gap-2">
                                    <span class="text-xs text-slate-400 dark:text-slate-500">
                                        {{ $schedule->attendance_sessions_count }} sesi tercatat
                                    </span>

                                    @if ($schedule->is_active)
                                        <a href="{{ route('guru.attendance.fill', ['schedule' => $schedule, 'date' => now()->toDateString()]) }}"
                                           class="btn-ghost btn-sm">
                                            <x-icon name="clipboard-document-check" class="h-4 w-4" />
                                            Isi Absensi
                                        </a>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
