<x-layouts.app :title="'Rekap Absensi'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <p class="page-subtitle">
            Pantau dan ekspor catatan absensi seluruh kelas. Admin bersifat baca-saja —
            koreksi absensi hanya dapat dilakukan oleh guru pemilik jadwal pada jendela edit {{ config('simas.attendance.edit_window_days') }} hari.
        </p>

        <a href="{{ route('admin.attendance.export', array_filter($filters, fn ($value) => $value !== null && $value !== '')) }}"
           class="btn-gold">
            <x-icon name="arrow-down-tray" class="h-4 w-4" />
            Ekspor CSV
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.attendance.index') }}" class="card-pad mb-5">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="date_from" class="form-label">Dari Tanggal</label>
                <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] }}"
                       class="form-input @error('date_from') is-invalid @enderror">
            </div>

            <div>
                <label for="date_to" class="form-label">Sampai Tanggal</label>
                <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] }}"
                       class="form-input @error('date_to') is-invalid @enderror">
                @error('date_to') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="class_id" class="form-label">Kelas</label>
                <select id="class_id" name="class_id" class="form-select">
                    <option value="">Semua kelas</option>
                    @foreach ($options['classes'] as $class)
                        <option value="{{ $class->id }}" @selected((string) ($filters['class_id'] ?? '') === (string) $class->id)>
                            {{ $class->displayName() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\Attendance::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="subject_id" class="form-label">Mata Pelajaran</label>
                <select id="subject_id" name="subject_id" class="form-select">
                    <option value="">Semua mapel</option>
                    @foreach ($options['subjects'] as $subject)
                        <option value="{{ $subject->id }}" @selected((string) ($filters['subject_id'] ?? '') === (string) $subject->id)>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="teacher_id" class="form-label">Guru</label>
                <select id="teacher_id" name="teacher_id" class="form-select">
                    <option value="">Semua guru</option>
                    @foreach ($options['teachers'] as $teacher)
                        <option value="{{ $teacher->id }}" @selected((string) ($filters['teacher_id'] ?? '') === (string) $teacher->id)>
                            {{ $teacher->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label for="q" class="form-label">Cari Siswa</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-icon name="magnifying-glass" class="h-4 w-4" />
                    </span>
                    <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}"
                           class="form-input pl-10" placeholder="Nama atau username siswa…">
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button type="submit" class="btn-primary btn-sm">
                <x-icon name="funnel" class="h-4 w-4" />
                Terapkan Filter
            </button>
            <a href="{{ route('admin.attendance.index') }}" class="btn-ghost btn-sm">Reset</a>
        </div>
    </form>

    {{-- Ringkasan periode --}}
    <div class="mb-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <x-stat-card label="Hadir" :value="$totals['hadir']" icon="check-circle" tone="emerald" animate />
        <x-stat-card label="Izin" :value="$totals['izin']" icon="document-text" tone="sky" animate />
        <x-stat-card label="Sakit" :value="$totals['sakit']" icon="heart" tone="gold" animate />
        <x-stat-card label="Alpa" :value="$totals['alpa']" icon="x-circle" tone="rose" animate />
        <x-stat-card label="Persentase Kehadiran"
                     :value="$totals['percentage'] !== null ? $totals['percentage'].'%' : 'Belum ada data'"
                     icon="chart-pie"
                     :hint="'Total '.number_format($totals['total'], 0, ',', '.').' catatan'"
                     tone="primary" />
    </div>

    {{-- Ringkasan per kelas --}}
    @if ($classSummaries->isNotEmpty())
        <div class="table-wrap mb-5">
            <div class="table-heading">
                <h2 class="section-title">Ringkasan per Kelas</h2>
                <p class="table-caption">Sesuai periode dan filter aktif</p>
            </div>

            <table class="table-simas">
                <thead>
                    <tr>
                        <th scope="col">Kelas</th>
                        <th scope="col">Hadir</th>
                        <th scope="col">Izin</th>
                        <th scope="col">Sakit</th>
                        <th scope="col">Alpa</th>
                        <th scope="col">Total</th>
                        <th scope="col">Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($classSummaries as $summary)
                        <tr>
                            <td>
                                <a href="{{ route('admin.attendance.index', array_merge(array_filter($filters, fn ($value) => $value !== null && $value !== ''), ['class_id' => $summary['class_id']])) }}"
                                   class="font-medium text-primary-600 hover:text-primary-700 dark:text-primary-300">
                                    {{ $summary['class_name'] }}
                                </a>
                                <span class="text-xs text-slate-500 dark:text-slate-400">· {{ $summary['academic_year'] }}</span>
                            </td>
                            <td class="tabular-nums">{{ number_format($summary['hadir'], 0, ',', '.') }}</td>
                            <td class="tabular-nums">{{ number_format($summary['izin'], 0, ',', '.') }}</td>
                            <td class="tabular-nums">{{ number_format($summary['sakit'], 0, ',', '.') }}</td>
                            <td class="tabular-nums">{{ number_format($summary['alpa'], 0, ',', '.') }}</td>
                            <td class="tabular-nums">{{ number_format($summary['total'], 0, ',', '.') }}</td>
                            <td>
                                @if ($summary['percentage'] !== null)
                                    <span class="font-semibold tabular-nums text-primary-700 dark:text-primary-200">{{ $summary['percentage'] }}%</span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Daftar catatan --}}
    <div class="table-wrap">
        <div class="table-heading">
            <h2 class="section-title">Catatan Absensi</h2>
            <p class="table-caption">{{ $attendances->total() }} catatan</p>
        </div>

        @if ($attendances->isEmpty())
            <x-empty-state icon="table-cells"
                           title="Tidak ada catatan absensi"
                           description="Tidak ada data pada periode dan filter yang dipilih. Coba ubah rentang tanggal atau filter lainnya." />
        @else
            <table class="table-simas">
                <thead>
                    <tr>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Siswa</th>
                        <th scope="col">Kelas &amp; Mapel</th>
                        <th scope="col">Guru</th>
                        <th scope="col">Status</th>
                        <th scope="col">Catatan</th>
                        <th scope="col" class="!text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $attendance)
                        <tr>
                            <td class="whitespace-nowrap">{{ $attendance->session->session_date->isoFormat('ddd, D MMM Y') }}</td>
                            <td>
                                <p class="font-medium text-primary-900 dark:text-white">{{ $attendance->student->user->name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">NIS {{ $attendance->student->student_number }}</p>
                            </td>
                            <td>
                                <p>{{ $attendance->session->schedule?->subject?->name ?? '—' }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $attendance->session->schedule?->schoolClass?->name }}</p>
                            </td>
                            <td>{{ $attendance->session->schedule?->teacher?->user?->name ?? '—' }}</td>
                            <td><x-status-badge :status="$attendance->status" /></td>
                            <td class="max-w-[14rem]">
                                <span class="line-clamp-2 text-sm text-slate-600 dark:text-slate-300">{{ $attendance->note ?? '—' }}</span>
                            </td>
                            <td class="!text-right">
                                <a href="{{ route('admin.attendance.sessions.show', $attendance->attendance_session_id) }}"
                                   class="btn-ghost btn-sm" title="Detail sesi" aria-label="Detail sesi absensi">
                                    <x-icon name="eye" class="h-4 w-4" />
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="mt-5">
        {{ $attendances->links() }}
    </div>
</x-layouts.app>
