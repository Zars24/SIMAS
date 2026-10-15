<x-layouts.app :title="'Dashboard Admin'">
    @push('scripts')
        <script>
            window.SIMAS_onReady(function () {
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
                                y: {
                                    min: 0,
                                    max: 100,
                                    ticks: { callback: function (value) { return value + '%'; } },
                                },
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

                var distributionCanvas = document.getElementById('distributionChart');
                var distribution = @json($periodCounts);

                if (distributionCanvas && Object.values(distribution).some(function (value) { return value > 0; })) {
                    new Chart(distributionCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: ['Hadir', 'Izin', 'Sakit', 'Alpa'],
                            datasets: [{
                                data: [distribution.hadir, distribution.izin, distribution.sakit, distribution.alpa],
                                backgroundColor: [palette.hadir, palette.izin, palette.sakit, palette.alpa],
                                borderWidth: 0,
                                hoverOffset: 6,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '68%',
                            plugins: {
                                legend: { position: 'bottom' },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) { return context.label + ': ' + context.parsed + ' catatan'; },
                                    },
                                },
                            },
                        },
                    });
                }
            });
        </script>
    @endpush

    {{-- Baris kontrol --}}
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="page-subtitle">
                Ringkasan operasional {{ config('simas.school_name') }} — {{ now()->translatedFormat('l, d F Y') }}.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center rounded-xl border border-primary-200 bg-white p-0.5 shadow-sm dark:border-primary-700 dark:bg-primary-900/50" role="group" aria-label="Filter periode">
                @foreach ([7 => '7 hari', 30 => '30 hari', 90 => '90 hari'] as $days => $label)
                    <a href="{{ route('admin.dashboard', ['period' => $days]) }}"
                       class="rounded-[0.6rem] px-3 py-1.5 text-xs font-semibold transition {{ $period === $days ? 'bg-primary-500 text-white shadow-sm' : 'text-primary-700 hover:bg-primary-100 dark:text-primary-200 dark:hover:bg-primary-900' }}"
                       @if ($period === $days) aria-current="true" @endif>
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <a href="{{ route('admin.attendance.index') }}" class="btn-outline btn-sm">
                <x-icon name="table-cells" class="h-4 w-4" />
                Rekap Absensi
            </a>
        </div>
    </div>

    {{-- Statistik utama --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Siswa Aktif" :value="$stats['students']" icon="academic-cap" tone="primary" animate />
        <x-stat-card label="Guru Aktif" :value="$stats['teachers']" icon="briefcase" tone="sky" animate />
        <x-stat-card label="Kelas Aktif" :value="$stats['classes']" icon="building-library" tone="emerald" animate />
        <x-stat-card label="Sesi Absensi Hari Ini" :value="$stats['sessions_today']" icon="clipboard-document-check" tone="gold" animate
                     :hint="$todaySchedules->count().' jadwal · '.$todaySchedules->where('filled_today', false)->count().' belum diisi'" />
    </div>

    {{-- Grafik --}}
    <div class="mt-5 grid gap-4 lg:grid-cols-3">
        <div class="card-pad lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="section-title">Tren Kehadiran</h2>
                    <p class="page-subtitle">Persentase hadir per hari, {{ $period }} hari terakhir.</p>
                </div>
                <span class="badge-gold">Periode {{ $period }} hari</span>
            </div>

            <div class="mt-4 h-72">
                @if (array_sum($trend['total']) > 0)
                    <canvas id="trendChart" role="img" aria-label="Grafik tren persentase kehadiran"></canvas>
                @else
                    <x-empty-state icon="presentation-chart-line" title="Belum ada data kehadiran"
                                   description="Grafik akan muncul setelah guru mengisi absensi pada periode ini." class="h-full" />
                @endif
            </div>
        </div>

        <div class="card-pad">
            <h2 class="section-title">Distribusi Status</h2>
            <p class="page-subtitle">Seluruh catatan pada periode terpilih.</p>

            <div class="mt-4 flex items-center justify-center">
                @if (array_sum($periodCounts) > 0)
                    <div class="h-56 w-full">
                        <canvas id="distributionChart" role="img" aria-label="Grafik distribusi status absensi"></canvas>
                    </div>
                @else
                    <x-empty-state icon="chart-pie" title="Belum ada catatan" description="Distribusi status akan tampil di sini." />
                @endif
            </div>

            <div class="mt-4 grid grid-cols-2 gap-2">
                <div class="rounded-xl bg-emerald-50 px-3 py-2 dark:bg-emerald-500/10">
                    <p class="text-xs font-medium text-emerald-700 dark:text-emerald-300">Hadir</p>
                    <p class="text-lg font-bold text-emerald-800 dark:text-emerald-200">{{ $periodCounts['hadir'] }}</p>
                </div>
                <div class="rounded-xl bg-amber-50 px-3 py-2 dark:bg-amber-500/10">
                    <p class="text-xs font-medium text-amber-700 dark:text-amber-300">Izin</p>
                    <p class="text-lg font-bold text-amber-800 dark:text-amber-200">{{ $periodCounts['izin'] }}</p>
                </div>
                <div class="rounded-xl bg-sky-50 px-3 py-2 dark:bg-sky-500/10">
                    <p class="text-xs font-medium text-sky-700 dark:text-sky-300">Sakit</p>
                    <p class="text-lg font-bold text-sky-800 dark:text-sky-200">{{ $periodCounts['sakit'] }}</p>
                </div>
                <div class="rounded-xl bg-rose-50 px-3 py-2 dark:bg-rose-500/10">
                    <p class="text-xs font-medium text-rose-700 dark:text-rose-300">Alpa</p>
                    <p class="text-lg font-bold text-rose-800 dark:text-rose-200">{{ $periodCounts['alpa'] }}</p>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-between rounded-xl border border-primary-900/[0.08] px-3.5 py-3 dark:border-white/10">
                <span class="text-sm text-slate-500 dark:text-slate-400">Persentase hadir</span>
                @if ($periodPercentage !== null)
                    <span class="text-xl font-bold text-primary-700 dark:text-primary-200">{{ number_format($periodPercentage, 1, ',', '.') }}%</span>
                @else
                    <span class="text-sm font-medium text-slate-400">Belum ada data</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Jadwal hari ini & aktivitas --}}
    <div class="mt-5 grid gap-4 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-primary-900/[0.08] px-5 py-4 dark:border-white/10">
                <div>
                    <h2 class="section-title">Jadwal Hari Ini</h2>
                    <p class="page-subtitle">Status pengisian absensi tiap sesi.</p>
                </div>
                <span class="badge-neutral">{{ $todaySchedules->count() }} sesi</span>
            </div>

            @if ($todaySchedules->isEmpty())
                <x-empty-state icon="calendar-days" title="Tidak ada jadwal hari ini"
                               description="Hari ini tidak ada sesi mengajar terjadwal." />
            @else
                <div class="overflow-x-auto">
                    <table class="table-simas">
                        <thead>
                            <tr>
                                <th scope="col">Jam</th>
                                <th scope="col">Kelas</th>
                                <th scope="col">Mata Pelajaran</th>
                                <th scope="col">Guru</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="!text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($todaySchedules as $schedule)
                                <tr>
                                    <td class="font-medium">{{ $schedule->timeRange() }}</td>
                                    <td>{{ $schedule->schoolClass?->name ?? '—' }}</td>
                                    <td>{{ $schedule->subject?->name ?? '—' }}</td>
                                    <td>{{ $schedule->teacher?->user?->name ?? '—' }}</td>
                                    <td>
                                        @if ($schedule->filled_today)
                                            <x-status-badge status="hadir" label="Sudah diisi" />
                                        @else
                                            <x-status-badge status="belum" />
                                        @endif
                                    </td>
                                    <td class="!text-right">
                                        <a href="{{ route('admin.attendance.index', ['class_id' => $schedule->class_id, 'subject_id' => $schedule->subject_id, 'date_from' => now()->toDateString(), 'date_to' => now()->toDateString()]) }}"
                                           class="btn-ghost btn-sm">Lihat</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="card">
            <div class="flex items-center justify-between gap-2 border-b border-primary-900/[0.08] px-5 py-4 dark:border-white/10">
                <h2 class="section-title">Aktivitas Terbaru</h2>
                <a href="{{ route('admin.audit-logs.index') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-300">
                    Lihat semua
                </a>
            </div>

            @if ($recentLogs->isEmpty())
                <x-empty-state icon="document-text" title="Belum ada aktivitas"
                               description="Perubahan data penting akan tercatat di sini." />
            @else
                <ul class="divide-y divide-primary-900/[0.06] dark:divide-white/[0.06]">
                    @foreach ($recentLogs as $log)
                        <li class="flex items-start gap-3 px-5 py-3.5">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-500/10 dark:text-primary-300">
                                <x-icon name="clock" class="h-4 w-4" />
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">
                                    {{ $log->user?->name ?? 'Sistem' }}
                                    <span class="font-normal text-slate-500 dark:text-slate-400">— {{ $log->action }}</span>
                                </p>
                                <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500">
                                    {{ \Illuminate\Support\Carbon::parse($log->created_at)->diffForHumans() }}
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-layouts.app>
