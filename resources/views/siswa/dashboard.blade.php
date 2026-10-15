<x-layouts.app :title="'Dashboard Siswa'">
    @if (! $student)
        <div class="card-pad">
            <x-empty-state icon="academic-cap"
                           title="Profil siswa belum tersedia"
                           description="Akun Anda belum ditautkan ke profil siswa. Hubungi admin agar profil dan kelas Anda diatur." />
        </div>
    @else
        @push('scripts')
            <script>
                window.SIMAS_onReady(function () {
                    var palette = window.SIMAS_PALETTE;
                    var summary = @json($summary);
                    var canvas = document.getElementById('summaryChart');

                    if (canvas && Object.values(summary).some(function (value) { return value > 0; })) {
                        new Chart(canvas, {
                            type: 'doughnut',
                            data: {
                                labels: ['Hadir', 'Izin', 'Sakit', 'Alpa'],
                                datasets: [{
                                    data: [summary.hadir, summary.izin, summary.sakit, summary.alpa],
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

        {{-- Sambutan --}}
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <p class="page-subtitle">
                Halo, {{ auth()->user()->name }}. Ringkasan kehadiran dan jadwal Anda — {{ now()->translatedFormat('l, d F Y') }}.
            </p>

            <a href="{{ route('siswa.attendance') }}" class="btn-outline btn-sm">
                <x-icon name="clipboard-document-list" class="h-4 w-4" />
                Riwayat Absensi
            </a>
        </div>

        {{-- Statistik pribadi --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Hadir" :value="$summary['hadir']" icon="check-circle" tone="emerald" animate />
            <x-stat-card label="Izin" :value="$summary['izin']" icon="document-text" tone="sky" animate />
            <x-stat-card label="Sakit" :value="$summary['sakit']" icon="heart" tone="gold" animate />
            <x-stat-card label="Alpa" :value="$summary['alpa']" icon="x-circle" tone="rose" animate />
        </div>

        <div class="mt-5 grid gap-4 lg:grid-cols-3">
            {{-- Distribusi & persentase --}}
            <div class="card-pad">
                <h2 class="section-title">Distribusi Kehadiran</h2>
                <p class="page-subtitle">Seluruh catatan absensi Anda.</p>

                <div class="mt-4 flex items-center justify-center">
                    @if (array_sum($summary) > 0)
                        <div class="h-52 w-full">
                            <canvas id="summaryChart" role="img" aria-label="Grafik distribusi status absensi Anda"></canvas>
                        </div>
                    @else
                        <x-empty-state icon="chart-pie" title="Belum ada data"
                                       description="Distribusi akan tampil setelah guru mengisi absensi." />
                    @endif
                </div>

                <div class="mt-4 flex items-center justify-between rounded-xl border border-primary-900/[0.08] px-3.5 py-3 dark:border-white/10">
                    <span class="text-sm text-slate-500 dark:text-slate-400">Persentase kehadiran</span>
                    @if ($percentage !== null)
                        <span class="text-xl font-bold text-primary-700 dark:text-primary-200">{{ number_format($percentage, 1, ',', '.') }}%</span>
                    @else
                        <span class="text-sm font-medium text-slate-400">Belum ada data</span>
                    @endif
                </div>
            </div>

            {{-- Jadwal terdekat --}}
            <div class="card lg:col-span-2">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-primary-900/[0.08] px-5 py-4 dark:border-white/10">
                    <div>
                        <h2 class="section-title">Jadwal Terdekat</h2>
                        <p class="page-subtitle">
                            @if ($upcoming)
                                {{ $upcoming['date']->translatedFormat('l, d F Y') }}
                            @else
                                Jadwal kelas Anda
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('siswa.schedule') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-300">
                        Jadwal lengkap
                    </a>
                </div>

                @if (! $upcoming)
                    <x-empty-state icon="calendar-days" title="Belum ada jadwal"
                                   description="Jadwal kelas akan muncul setelah admin menyusunnya." />
                @else
                    <div class="overflow-x-auto">
                        <table class="table-simas">
                            <thead>
                                <tr>
                                    <th scope="col">Jam</th>
                                    <th scope="col">Mata Pelajaran</th>
                                    <th scope="col">Guru</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($upcoming['schedules'] as $schedule)
                                    <tr>
                                        <td class="font-medium tabular-nums">{{ $schedule->timeRange() }}</td>
                                        <td>{{ $schedule->subject?->name ?? '—' }}</td>
                                        <td>{{ $schedule->teacher?->user?->name ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Kehadiran bulan ini & riwayat terbaru --}}
        <div class="mt-5 grid gap-4 lg:grid-cols-3">
            <div class="card-pad">
                <h2 class="section-title">Bulan Ini</h2>
                <p class="page-subtitle">{{ now()->translatedFormat('F Y') }}</p>

                <ul class="mt-4 space-y-2">
                    <li class="flex items-center justify-between rounded-xl bg-emerald-50 px-3.5 py-2.5 dark:bg-emerald-500/10">
                        <span class="text-sm font-medium text-emerald-700 dark:text-emerald-300">Hadir</span>
                        <span class="font-bold tabular-nums text-emerald-800 dark:text-emerald-200">{{ $monthCounts['hadir'] }}</span>
                    </li>
                    <li class="flex items-center justify-between rounded-xl bg-amber-50 px-3.5 py-2.5 dark:bg-amber-500/10">
                        <span class="text-sm font-medium text-amber-700 dark:text-amber-300">Izin</span>
                        <span class="font-bold tabular-nums text-amber-800 dark:text-amber-200">{{ $monthCounts['izin'] }}</span>
                    </li>
                    <li class="flex items-center justify-between rounded-xl bg-sky-50 px-3.5 py-2.5 dark:bg-sky-500/10">
                        <span class="text-sm font-medium text-sky-700 dark:text-sky-300">Sakit</span>
                        <span class="font-bold tabular-nums text-sky-800 dark:text-sky-200">{{ $monthCounts['sakit'] }}</span>
                    </li>
                    <li class="flex items-center justify-between rounded-xl bg-rose-50 px-3.5 py-2.5 dark:bg-rose-500/10">
                        <span class="text-sm font-medium text-rose-700 dark:text-rose-300">Alpa</span>
                        <span class="font-bold tabular-nums text-rose-800 dark:text-rose-200">{{ $monthCounts['alpa'] }}</span>
                    </li>
                </ul>

                <div class="mt-4 flex items-center justify-between rounded-xl border border-primary-900/[0.08] px-3.5 py-3 dark:border-white/10">
                    <span class="text-sm text-slate-500 dark:text-slate-400">Kehadiran bulan ini</span>
                    @if ($monthPercentage !== null)
                        <span class="text-lg font-bold text-primary-700 dark:text-primary-200">{{ number_format($monthPercentage, 1, ',', '.') }}%</span>
                    @else
                        <span class="text-sm font-medium text-slate-400">Belum ada data</span>
                    @endif
                </div>
            </div>

            <div class="card lg:col-span-2">
                <div class="flex items-center justify-between gap-2 border-b border-primary-900/[0.08] px-5 py-4 dark:border-white/10">
                    <h2 class="section-title">Riwayat Terbaru</h2>
                    <a href="{{ route('siswa.attendance') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-300">
                        Lihat semua
                    </a>
                </div>

                @if ($recentAttendances->isEmpty())
                    <x-empty-state icon="clipboard-document-list" title="Belum ada riwayat"
                                   description="Catatan absensi Anda akan tampil di sini." />
                @else
                    <ul class="divide-y divide-primary-900/[0.06] dark:divide-white/[0.06]">
                        @foreach ($recentAttendances as $attendance)
                            <li class="flex items-center justify-between gap-3 px-5 py-3.5">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">
                                        {{ $attendance->session->schedule?->subject?->name ?? '—' }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500">
                                        {{ $attendance->session->session_date->isoFormat('ddd, D MMM Y') }}
                                        · {{ $attendance->session->schedule?->teacher?->user?->name ?? '—' }}
                                    </p>
                                </div>
                                <x-status-badge :status="$attendance->status" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endif
</x-layouts.app>
