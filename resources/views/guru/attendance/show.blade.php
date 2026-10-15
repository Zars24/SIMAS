<x-layouts.app :title="'Ringkasan Sesi Absensi'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('guru.attendance.index', ['date' => $session->session_date->toDateString()]) }}" class="btn-ghost btn-sm -ml-2">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali
        </a>

        @if ($canEdit)
            <a href="{{ route('guru.attendance.fill', ['schedule' => $session->schedule, 'date' => $session->session_date->toDateString()]) }}"
               class="btn-outline btn-sm">
                <x-icon name="pencil-square" class="h-4 w-4" />
                Koreksi Absensi
            </a>
        @endif
    </div>

    {{-- Info sesi --}}
    <div class="card-pad mb-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-lg font-bold text-primary-900 dark:text-white">{{ $session->schedule?->subject?->name }}</h2>
                    @if ($session->isSubmitted())
                        <span class="badge-emerald">Tersimpan</span>
                    @endif
                    @if (! $canEdit)
                        <span class="badge-slate">Di luar jendela koreksi</span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $session->schedule?->schoolClass?->displayName() }} · {{ $session->schedule?->timeRange() }}
                </p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $session->session_date->isoFormat('dddd, D MMMM Y') }}
                    @if ($session->topic) · Materi: {{ $session->topic }} @endif
                </p>
            </div>

            <div class="text-right text-xs text-slate-500 dark:text-slate-400">
                <p>Disimpan @tanggaljam($session->submitted_at)</p>
                @unless ($canEdit)
                    <p class="mt-1 max-w-[16rem]">
                        Koreksi hanya dapat dilakukan dalam {{ config('simas.attendance.edit_window_days') }} hari setelah sesi dibuat.
                    </p>
                @endunless
            </div>
        </div>

        @php
            $total = array_sum($counts);
            $percentage = $total > 0 ? round($counts['hadir'] / $total * 100, 1) : null;
        @endphp

        <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-primary-900/[0.08] pt-4 dark:border-white/10">
            <span class="badge-hadir">Hadir {{ $counts['hadir'] }}</span>
            <span class="badge-izin">Izin {{ $counts['izin'] }}</span>
            <span class="badge-sakit">Sakit {{ $counts['sakit'] }}</span>
            <span class="badge-alpa">Alpa {{ $counts['alpa'] }}</span>
            <span class="badge-neutral">Total {{ $total }} siswa</span>
            @if ($percentage !== null)
                <span class="badge-gold">Kehadiran {{ $percentage }}%</span>
            @endif
        </div>
    </div>

    {{-- Daftar siswa --}}
    <div class="table-wrap">
        <div class="table-heading">
            <h2 class="section-title">Status per Siswa</h2>
            <p class="table-caption">{{ $session->attendances->count() }} catatan</p>
        </div>

        @if ($session->attendances->isEmpty())
            <x-empty-state icon="clipboard-document-list"
                           title="Belum ada catatan"
                           description="Sesi ini belum memiliki catatan absensi." />
        @else
            <table class="table-simas">
                <thead>
                    <tr>
                        <th scope="col" class="w-12">No</th>
                        <th scope="col">Siswa</th>
                        <th scope="col">Status</th>
                        <th scope="col">Catatan</th>
                        <th scope="col">Dicatat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($session->attendances->sortBy(fn ($attendance) => $attendance->student->user->name) as $index => $attendance)
                        <tr>
                            <td class="tabular-nums text-slate-400 dark:text-slate-500">{{ $index + 1 }}</td>
                            <td>
                                <p class="font-medium text-primary-900 dark:text-white">{{ $attendance->student->user->name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">NIS {{ $attendance->student->student_number }}</p>
                            </td>
                            <td><x-status-badge :status="$attendance->status" /></td>
                            <td class="max-w-[20rem]">
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
</x-layouts.app>
