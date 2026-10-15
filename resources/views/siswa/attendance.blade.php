<x-layouts.app :title="'Riwayat Absensi Saya'">
    @if (! $student)
        <div class="card-pad">
            <x-empty-state icon="user-circle"
                           title="Profil belum tersedia"
                           description="Akun Anda belum ditautkan ke profil siswa." />
        </div>
    @else
        <div class="mb-5">
            <p class="page-subtitle">
                Riwayat kehadiran pribadi Anda. Data ini hanya menampilkan absensi milik akun Anda sendiri.
            </p>
        </div>

        {{-- Ringkasan mengikuti periode --}}
        <div class="mb-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <x-stat-card label="Hadir" :value="$summary['hadir']" icon="check-circle" tone="emerald" animate />
            <x-stat-card label="Izin" :value="$summary['izin']" icon="document-text" tone="sky" animate />
            <x-stat-card label="Sakit" :value="$summary['sakit']" icon="heart" tone="gold" animate />
            <x-stat-card label="Alpa" :value="$summary['alpa']" icon="x-circle" tone="rose" animate />
            <x-stat-card label="Persentase Kehadiran"
                         :value="$percentage !== null ? $percentage.'%' : 'Belum ada data'"
                         icon="chart-pie"
                         :hint="'Total '.array_sum($summary).' catatan'"
                         tone="primary" />
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('siswa.attendance') }}" class="card-pad mb-5">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="month" class="form-label">Bulan</label>
                    <input id="month" name="month" type="month" value="{{ $filters['month'] ?? '' }}"
                           class="form-input @error('month') is-invalid @enderror">
                    @error('month') <p class="form-error">{{ $message }}</p> @enderror
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
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected((string) ($filters['subject_id'] ?? '') === (string) $subject->id)>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary">
                        <x-icon name="funnel" class="h-4 w-4" />
                        Terapkan
                    </button>
                    <a href="{{ route('siswa.attendance') }}" class="btn-ghost">Reset</a>
                </div>
            </div>
        </form>

        {{-- Tabel riwayat --}}
        <div class="table-wrap">
            <div class="table-heading">
                <h2 class="section-title">Riwayat Absensi</h2>
                <p class="table-caption">{{ $attendances->total() }} catatan</p>
            </div>

            @if ($attendances->isEmpty())
                <x-empty-state icon="clipboard-document-list"
                               title="Belum ada riwayat absensi"
                               description="Tidak ada catatan pada filter ini. Coba ubah bulan atau status." />
            @else
                <table class="table-simas">
                    <thead>
                        <tr>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Mata Pelajaran</th>
                            <th scope="col">Guru</th>
                            <th scope="col">Status</th>
                            <th scope="col">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attendances as $attendance)
                            <tr>
                                <td class="whitespace-nowrap">{{ $attendance->session->session_date->isoFormat('ddd, D MMM Y') }}</td>
                                <td>
                                    <p class="font-medium text-primary-900 dark:text-white">
                                        {{ $attendance->session->schedule?->subject?->name ?? '—' }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $attendance->session->schedule?->schoolClass?->name }}
                                    </p>
                                </td>
                                <td>{{ $attendance->session->schedule?->teacher?->user?->name ?? '—' }}</td>
                                <td><x-status-badge :status="$attendance->status" /></td>
                                <td class="max-w-[20rem]">
                                    <span class="line-clamp-2 text-sm text-slate-600 dark:text-slate-300">{{ $attendance->note ?? '—' }}</span>
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
    @endif
</x-layouts.app>
