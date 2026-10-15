<x-layouts.app :title="'Detail Siswa: '.$student->user->name">
    <div class="mb-5">
        <a href="{{ route('admin.students.index') }}" class="btn-ghost btn-sm -ml-2">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke daftar
        </a>
        <p class="page-subtitle mt-2">Profil, ringkasan kehadiran, dan riwayat absensi siswa.</p>
    </div>

    {{-- Kartu profil --}}
    <div class="card-pad mb-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-primary-500 text-lg font-bold text-white">
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($student->user->name, 0, 2)) }}
                </span>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-lg font-bold text-primary-900 dark:text-white">{{ $student->user->name }}</h2>
                        @if ($student->user->is_active)
                            <span class="badge-emerald">Aktif</span>
                        @else
                            <span class="badge-slate">Nonaktif</span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        NIS {{ $student->student_number }} · {{ '@'.$student->user->username }} · {{ $student->user->email }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.students.edit', $student) }}" class="btn-outline btn-sm">
                    <x-icon name="pencil-square" class="h-4 w-4" />
                    Ubah
                </a>
            </div>
        </div>

        <dl class="mt-6 grid gap-x-6 gap-y-4 border-t border-primary-900/[0.08] pt-5 dark:border-white/10 sm:grid-cols-2 lg:grid-cols-4">
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
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tanggal Lahir</dt>
                <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                    {{ $student->birth_date ? $student->birth_date->isoFormat('D MMMM Y') : '—' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Telepon</dt>
                <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $student->phone ?? '—' }}</dd>
            </div>
            <div class="sm:col-span-2 lg:col-span-4">
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Alamat</dt>
                <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $student->address ?? '—' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Ringkasan kehadiran --}}
    <div class="mb-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <x-stat-card label="Hadir" :value="$summary['hadir']" icon="check-circle" tone="emerald" animate />
        <x-stat-card label="Izin" :value="$summary['izin']" icon="document-text" tone="sky" animate />
        <x-stat-card label="Sakit" :value="$summary['sakit']" icon="heart" tone="gold" animate />
        <x-stat-card label="Alpa" :value="$summary['alpa']" icon="x-circle" tone="rose" animate />
        <x-stat-card label="Persentase Kehadiran"
                     :value="$percentage !== null ? $percentage.'%' : 'Belum ada data'"
                     icon="chart-pie"
                     :hint="$percentage !== null ? 'Hadir ÷ total catatan absensi' : 'Belum ada catatan absensi'"
                     tone="primary" />
    </div>

    {{-- Riwayat absensi --}}
    <div class="table-wrap">
        <div class="table-heading">
            <h2 class="section-title">Riwayat Absensi</h2>
            <p class="table-caption">{{ $attendances->total() }} catatan</p>
        </div>

        @if ($attendances->isEmpty())
            <x-empty-state icon="clipboard-document-list"
                           title="Belum ada riwayat absensi"
                           description="Catatan absensi akan muncul setelah guru mengisi absensi pada sesi pembelajaran." />
        @else
            <table class="table-simas">
                <thead>
                    <tr>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Mata Pelajaran</th>
                        <th scope="col">Guru</th>
                        <th scope="col">Status</th>
                        <th scope="col">Catatan</th>
                        <th scope="col">Dicatat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $attendance)
                        <tr>
                            <td class="whitespace-nowrap">
                                {{ $attendance->session->session_date->isoFormat('ddd, D MMM Y') }}
                            </td>
                            <td>
                                <p class="font-medium text-primary-900 dark:text-white">
                                    {{ $attendance->session->schedule?->subject?->name ?? '—' }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $attendance->session->schedule?->schoolClass?->name ?? '' }}
                                </p>
                            </td>
                            <td>{{ $attendance->session->schedule?->teacher?->user?->name ?? '—' }}</td>
                            <td><x-status-badge :status="$attendance->status" /></td>
                            <td class="max-w-[16rem]">
                                <span class="line-clamp-2 text-sm text-slate-600 dark:text-slate-300">{{ $attendance->note ?? '—' }}</span>
                            </td>
                            <td class="whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">
                                {{ $attendance->recorder?->name ?? '—' }}<br>
                                @tanggaljam($attendance->recorded_at)
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
