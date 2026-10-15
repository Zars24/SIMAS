<x-layouts.app :title="'Dashboard Guru'">
    @if (! $teacher)
        <div class="card-pad">
            <x-empty-state icon="briefcase"
                           title="Profil guru belum tersedia"
                           description="Akun Anda belum ditautkan ke profil guru. Hubungi admin agar profil guru dibuatkan dan jadwal mengajar ditugaskan." />
        </div>
    @else
        @push('scripts')
            <script>
                (function () {
                    var palette = window.SIMAS_PALETTE;
                    var trend = @json($trend);

                    var trendCanvas = document.getElementById('trendChart');
                    if (trendCanvas && trend.total.some(function (value) { return value > 0; })) {
                        new Chart(trendCanvas, {
                            type: 'line',
                            data: {
                                labels: trend.labels,
                                datasets: [{
                                    label: 'Persentase kehadiran',
                                    data: trend.percentage,
                                    borderColor: palette.primary,
                                    backgroundColor: 'rgba(83, 121, 99, 0.12)',
                                    pointBackgroundColor: '#ffffff',
                                    pointBorderColor: palette.primary,
                                    pointRadius: 3,
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.35,
                                    spanGaps: true,
                                }],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: { min: 0, max: 100, ticks: { callback: function (value) { return value + '%'; } } },
                                    x: { grid: { display: false } },
                                },
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: function (context) {
                                                var index = context.dataIndex;
                                                return context.parsed.y + '% hadir — ' + trend.hadir[index] + ' dari ' + trend.total[index] + ' catatan';
                                            },
                                        },
                                    },
                                },
                            },
                        });
                    }
                })();
            </script>
        @endpush

        {{-- Baris kontrol --}}
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <p class="page-subtitle">
                Selamat datang, {{ auth()->user()->name }}. Ringkasan tugas mengajar Anda — {{ now()->translatedFormat('l, d F Y') }}.
            </p>

            <a href="{{ route('guru.attendance.index') }}" class="btn-primary btn-sm">
                <x-icon name="clipboard-document-check" class="h-4 w-4" />
                Isi Absensi
            </a>
        </div>

        {{-- Statistik --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Jadwal Hari Ini" :value="$todaySchedules->count()" icon="calendar-days" tone="primary" animate
                         :hint="$todaySchedules->where('filled_today', false)->count().' belum diisi absensinya'" />
            <x-stat-card label="Kelas Diampu" :value="$classCount" icon="building-library" tone="emerald" animate />
            <x-stat-card label="Siswa Diampu" :value="$studentCount" icon="academic-cap" tone="sky" animate />
            <x-stat-card label="Kehadiran Bulan Ini"
                         :value="$monthPercentage !== null ? $monthPercentage.'%' : 'Belum ada data'"
                         icon="chart-pie" tone="gold"
                         :hint="'Total '.array_sum($monthCounts).' catatan bulan ini'" />
        </div>

        {{-- Jadwal hari ini --}}
        <div class="mt-5 card">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-primary-900/[0.08] px-5 py-4 dark:border-white/10">
                <div>
                    <h2 class="section-title">Jadwal Hari Ini</h2>
                    <p class="page-subtitle">Isi absensi setiap sesi yang sudah berlangsung.</p>
                </div>
                <span class="badge-neutral">{{ $todaySchedules->count() }} sesi</span>
            </div>

            @if ($todaySchedules->isEmpty())
                <x-empty-state icon="calendar-days" title="Tidak ada jadwal hari ini"
                               description="Hari ini Anda tidak memiliki sesi mengajar terjadwal." />
            @else
                <div class="overflow-x-auto">
                    <table class="table-simas">
                        <thead>
                            <tr>
                                <th scope="col">Jam</th>
                                <th scope="col">Kelas</th>
                                <th scope="col">Mata Pelajaran</th>
                                <th scope="col">Status Absensi</th>
                                <th scope="col" class="!text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($todaySchedules as $schedule)
                                <tr>
                                    <td class="font-medium tabular-nums">{{ $schedule->timeRange() }}</td>
                                    <td>{{ $schedule->schoolClass?->name ?? '—' }}</td>
                                    <td>{{ $schedule->subject?->name ?? '—' }}</td>
                                    <td>
                                        @if ($schedule->filled_today)
                                            <x-status-badge status="hadir" label="Sudah diisi" />
                                        @else
                                            <x-status-badge status="belum" />
                                        @endif
                                    </td>
                                    <td class="!text-right">
                                        <a href="{{ route('guru.attendance.fill', ['schedule' => $schedule, 'date' => now()->toDateString()]) }}"
                                           class="{{ $schedule->filled_today ? 'btn-ghost' : 'btn-primary' }} btn-sm">
                                            {{ $schedule->filled_today ? 'Lihat / Koreksi' : 'Isi Absensi' }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Tren & jadwal mingguan --}}
        <div class="mt-5 grid gap-4 lg:grid-cols-3">
            <div class="card-pad lg:col-span-2">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="section-title">Tren Kehadiran Kelas Anda</h2>
                        <p class="page-subtitle">Persentase hadir per hari, 14 hari terakhir.</p>
                    </div>
                </div>

                <div class="mt-4 h-72">
                    @if (array_sum($trend['total']) > 0)
                        <canvas id="trendChart" role="img" aria-label="Grafik tren persentase kehadiran"></canvas>
                    @else
                        <x-empty-state icon="presentation-chart-line" title="Belum ada data kehadiran"
                                       description="Grafik akan muncul setelah Anda mengisi absensi." class="h-full" />
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="flex items-center justify-between gap-2 border-b border-primary-900/[0.08] px-5 py-4 dark:border-white/10">
                    <h2 class="section-title">Jadwal Mingguan</h2>
                    <a href="{{ route('guru.schedules.index') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-300">
                        Lihat semua
                    </a>
                </div>

                @if ($weeklySchedules->isEmpty())
                    <x-empty-state icon="calendar-days" title="Belum ada jadwal"
                                   description="Anda belum ditugaskan pada jadwal apa pun." />
                @else
                    <ul class="divide-y divide-primary-900/[0.06] dark:divide-white/[0.06]">
                        @foreach (\App\Models\Schedule::DAY_ORDER as $dayKey)
                            @continue (! $weeklySchedules->has($dayKey))
                            <li class="px-5 py-3.5">
                                <p class="text-xs font-bold uppercase tracking-widest text-primary-400 dark:text-primary-300/70">
                                    {{ \App\Models\Schedule::DAYS[$dayKey] }}
                                </p>
                                <ul class="mt-1.5 space-y-1.5">
                                    @foreach ($weeklySchedules[$dayKey] as $schedule)
                                        <li class="flex items-center justify-between gap-3 text-sm">
                                            <span class="min-w-0 truncate text-slate-700 dark:text-slate-200">
                                                {{ $schedule->subject?->name }} · {{ $schedule->schoolClass?->name }}
                                            </span>
                                            <span class="shrink-0 tabular-nums text-xs text-slate-500 dark:text-slate-400">
                                                {{ $schedule->timeRange() }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endif
</x-layouts.app>
