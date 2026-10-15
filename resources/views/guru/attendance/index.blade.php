<x-layouts.app :title="'Isi &amp; Riwayat Absensi'">
    @if (! $teacher)
        <div class="card-pad">
            <x-empty-state icon="briefcase"
                           title="Profil guru belum tersedia"
                           description="Akun Anda belum ditautkan ke profil guru. Hubungi admin untuk menugaskan jadwal mengajar." />
        </div>
    @else
        {{-- Pilih tanggal --}}
        <div class="card-pad mb-5">
            <form method="GET" action="{{ route('guru.attendance.index') }}"
                  class="flex flex-wrap items-end gap-3">
                <div class="min-w-[12rem]">
                    <label for="date" class="form-label">Tanggal Sesi</label>
                    <input id="date" name="date" type="date" value="{{ $date->toDateString() }}"
                           max="{{ now()->toDateString() }}"
                           class="form-input @error('date') is-invalid @enderror">
                    @error('date') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn-primary">
                    <x-icon name="magnifying-glass" class="h-4 w-4" />
                    Tampilkan Jadwal
                </button>

                <div class="ml-auto text-sm text-slate-500 dark:text-slate-400">
                    <span class="font-medium text-primary-800 dark:text-primary-200">{{ $date->translatedFormat('l, d F Y') }}</span>
                </div>
            </form>
        </div>

        {{-- Jadwal pada tanggal terpilih --}}
        <div class="card mb-5">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-primary-900/[0.08] px-5 py-4 dark:border-white/10">
                <div>
                    <h2 class="section-title">Sesi Jadwal</h2>
                    <p class="page-subtitle">Pilih sesi untuk mengisi atau mengoreksi absensi.</p>
                </div>
                <span class="badge-neutral">{{ $schedules->count() }} sesi</span>
            </div>

            @if ($schedules->isEmpty())
                <x-empty-state icon="calendar-days"
                               title="Tidak ada jadwal pada tanggal ini"
                               description="Tidak ada sesi mengajar Anda pada hari yang dipilih. Coba pilih tanggal lain yang sesuai jadwal." />
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
                            @foreach ($schedules as $schedule)
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
                                        <a href="{{ route('guru.attendance.fill', ['schedule' => $schedule, 'date' => $date->toDateString()]) }}"
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

        {{-- Riwayat sesi --}}
        <div class="table-wrap">
            <div class="table-heading">
                <h2 class="section-title">Riwayat Sesi Absensi</h2>
                <p class="table-caption">{{ $recentSessions->total() }} sesi</p>
            </div>

            @if ($recentSessions->isEmpty())
                <x-empty-state icon="clipboard-document-list"
                               title="Belum ada sesi absensi"
                               description="Sesi absensi yang Anda simpan akan muncul di sini." />
            @else
                <table class="table-simas">
                    <thead>
                        <tr>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Kelas &amp; Mapel</th>
                            <th scope="col">Hadir</th>
                            <th scope="col">Izin</th>
                            <th scope="col">Sakit</th>
                            <th scope="col">Alpa</th>
                            <th scope="col">Materi</th>
                            <th scope="col" class="!text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentSessions as $session)
                            <tr>
                                <td class="whitespace-nowrap">{{ $session->session_date->isoFormat('ddd, D MMM Y') }}</td>
                                <td>
                                    <p class="font-medium text-primary-900 dark:text-white">{{ $session->schedule?->subject?->name ?? '—' }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $session->schedule?->schoolClass?->name }}</p>
                                </td>
                                <td class="tabular-nums font-semibold text-emerald-600 dark:text-emerald-400">{{ $session->count_hadir }}</td>
                                <td class="tabular-nums">{{ $session->count_izin }}</td>
                                <td class="tabular-nums">{{ $session->count_sakit }}</td>
                                <td class="tabular-nums font-semibold text-rose-600 dark:text-rose-400">{{ $session->count_alpa }}</td>
                                <td class="max-w-[14rem]">
                                    <span class="line-clamp-1 text-sm text-slate-600 dark:text-slate-300">{{ $session->topic ?: '—' }}</span>
                                </td>
                                <td class="!text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('guru.attendance.show', $session) }}" class="btn-ghost btn-sm"
                                           title="Lihat" aria-label="Lihat sesi absensi">
                                            <x-icon name="eye" class="h-4 w-4" />
                                        </a>

                                        @can('update', $session)
                                            <a href="{{ route('guru.attendance.fill', ['schedule' => $session->schedule, 'date' => $session->session_date->toDateString()]) }}"
                                               class="btn-ghost btn-sm" title="Koreksi" aria-label="Koreksi sesi absensi">
                                                <x-icon name="pencil-square" class="h-4 w-4" />
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="mt-5">
            {{ $recentSessions->links() }}
        </div>
    @endif
</x-layouts.app>
