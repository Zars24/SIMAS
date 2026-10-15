@php
    $initial = [];
    $studentIds = [];

    foreach ($students as $student) {
        $studentIds[] = $student->id;

        if ($record = $existing->get($student->id)) {
            $initial[$student->id] = $record->status;
        }
    }

    $hasNotes = $existing->contains(fn ($record) => filled($record->note));
@endphp

<x-layouts.app :title="$session ? 'Koreksi Absensi' : 'Isi Absensi'">
    <div class="mb-5">
        <a href="{{ route('guru.attendance.index', ['date' => $date->toDateString()]) }}" class="btn-ghost btn-sm -ml-2">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke daftar sesi
        </a>
    </div>

    {{-- Info sesi --}}
    <div class="card-pad mb-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-primary-100 text-primary-600 dark:bg-primary-500/15 dark:text-primary-300">
                    <x-icon name="clipboard-document-check" class="h-6 w-6" />
                </span>

                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-lg font-bold text-primary-900 dark:text-white">
                            {{ $schedule->subject?->name ?? 'Sesi Absensi' }}
                        </h2>

                        @if ($session)
                            <span class="badge-gold">Mode koreksi</span>
                        @else
                            <span class="badge-emerald">Sesi baru</span>
                        @endif
                    </div>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $schedule->schoolClass?->displayName() }} · {{ $schedule->timeRange() }}
                    </p>
                    <p class="mt-0.5 text-sm font-medium text-primary-700 dark:text-primary-200">
                        {{ $date->translatedFormat('l, d F Y') }}
                    </p>
                </div>
            </div>

            @if ($session)
                <p class="max-w-xs text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                    Sesi ini sudah pernah disimpan. Perubahan yang Anda lakukan akan tercatat di audit log.
                    Koreksi hanya diizinkan dalam {{ config('simas.attendance.edit_window_days') }} hari setelah sesi dibuat.
                </p>
            @endif
        </div>
    </div>

    @if ($students->isEmpty())
        <div class="card-pad">
            <x-empty-state icon="academic-cap"
                           title="Belum ada siswa di kelas ini"
                           description="Kelas ini belum memiliki siswa aktif. Hubungi admin untuk menempatkan siswa terlebih dahulu." />
        </div>
    @else
        <form method="POST"
              action="{{ $session ? route('guru.attendance.update', $session) : route('guru.attendance.store', $schedule) }}"
              x-data='attendanceForm(@js($initial), @js($studentIds))'>
            @csrf
            @if ($session)
                @method('PUT')
            @endif

            @unless ($session)
                <input type="hidden" name="session_date" value="{{ $date->toDateString() }}">
            @endunless

            {{-- Materi --}}
            <div class="card-pad mb-5">
                <label for="topic" class="form-label">Materi / Topik Pembelajaran</label>
                <input id="topic" name="topic" type="text" maxlength="255"
                       value="{{ old('topic', $session->topic ?? '') }}"
                       class="form-input @error('topic') is-invalid @enderror"
                       placeholder="Opsional — contoh: Persamaan kuadrat dan penerapannya">
                @error('topic') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            {{-- Isi cepat & ringkasan --}}
            <div class="card-pad mb-5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="mr-1 text-sm font-semibold text-primary-900 dark:text-white">Isi cepat:</span>

                    <button type="button" class="btn-outline btn-sm" @click="setAll('hadir')">
                        <x-icon name="check-circle" class="h-4 w-4" />
                        Semua Hadir
                    </button>

                    <button type="button" class="btn-outline btn-sm" @click="setAll('izin')">Semua Izin</button>
                    <button type="button" class="btn-outline btn-sm" @click="setAll('sakit')">Semua Sakit</button>
                    <button type="button" class="btn-outline btn-sm" @click="setAll('alpa')">Semua Alpa</button>

                    <button type="button" class="btn-ghost btn-sm" @click="resetAll()">
                        <x-icon name="arrow-path" class="h-4 w-4" />
                        Kosongkan
                    </button>

                    <button type="button" class="btn-ghost btn-sm" @click="showNotes = ! showNotes">
                        <x-icon name="pencil-square" class="h-4 w-4" />
                        <span x-text="showNotes ? 'Sembunyikan catatan' : 'Catatan per siswa'"></span>
                    </button>
                </div>

                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                    Tidak ada status otomatis — setiap siswa harus dikonfirmasi kehadirannya sebelum disimpan.
                </p>

                {{-- Ringkasan langsung --}}
                <div class="mt-4 grid grid-cols-2 gap-2 border-t border-primary-900/[0.08] pt-4 dark:border-white/10 sm:grid-cols-5">
                    <div class="rounded-xl bg-emerald-50 px-3 py-2 dark:bg-emerald-500/10">
                        <p class="text-xs font-medium text-emerald-700 dark:text-emerald-300">Hadir</p>
                        <p class="text-lg font-bold tabular-nums text-emerald-800 dark:text-emerald-200" x-text="counts.hadir">0</p>
                    </div>
                    <div class="rounded-xl bg-amber-50 px-3 py-2 dark:bg-amber-500/10">
                        <p class="text-xs font-medium text-amber-700 dark:text-amber-300">Izin</p>
                        <p class="text-lg font-bold tabular-nums text-amber-800 dark:text-amber-200" x-text="counts.izin">0</p>
                    </div>
                    <div class="rounded-xl bg-sky-50 px-3 py-2 dark:bg-sky-500/10">
                        <p class="text-xs font-medium text-sky-700 dark:text-sky-300">Sakit</p>
                        <p class="text-lg font-bold tabular-nums text-sky-800 dark:text-sky-200" x-text="counts.sakit">0</p>
                    </div>
                    <div class="rounded-xl bg-rose-50 px-3 py-2 dark:bg-rose-500/10">
                        <p class="text-xs font-medium text-rose-700 dark:text-rose-300">Alpa</p>
                        <p class="text-lg font-bold tabular-nums text-rose-800 dark:text-rose-200" x-text="counts.alpa">0</p>
                    </div>
                    <div class="rounded-xl border border-primary-900/[0.08] px-3 py-2 dark:border-white/10">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Belum diisi</p>
                        <p class="text-lg font-bold tabular-nums" :class="unfilled > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'" x-text="unfilled">0</p>
                    </div>
                </div>

                {{-- Peringatan belum lengkap --}}
                <div x-cloak x-show="unfilled > 0"
                     class="mt-4 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-400/30 dark:bg-amber-500/10">
                    <x-icon name="exclamation-triangle" class="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" />
                    <p class="text-sm text-amber-800 dark:text-amber-200">
                        Masih ada <span class="font-bold" x-text="unfilled"></span> siswa yang belum dipilih statusnya.
                        Lengkapi semua siswa sebelum menyimpan.
                    </p>
                </div>
            </div>

            @error('attendances')
                <div class="mb-5 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 dark:border-rose-400/30 dark:bg-rose-500/10">
                    <x-icon name="exclamation-circle" class="mt-0.5 h-5 w-5 shrink-0 text-rose-600 dark:text-rose-400" />
                    <p class="text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>
                </div>
            @enderror

            {{-- Daftar siswa --}}
            <div class="card">
                <div class="table-heading">
                    <h2 class="section-title">Daftar Siswa</h2>
                    <p class="table-caption">{{ $students->count() }} siswa aktif</p>
                </div>

                <ul class="divide-y divide-primary-900/[0.06] dark:divide-white/[0.06]">
                    @foreach ($students as $index => $student)
                        @php $record = $existing->get($student->id); @endphp

                        <li class="px-4 py-4 sm:px-5">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-bold tabular-nums text-primary-700 dark:bg-primary-500/15 dark:text-primary-200">
                                        {{ $index + 1 }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-medium text-primary-900 dark:text-white">{{ $student->user->name }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">NIS {{ $student->student_number }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-1.5" role="radiogroup" aria-label="Status absensi {{ $student->user->name }}">
                                    @foreach (\App\Models\Attendance::STATUSES as $value => $label)
                                        <label class="cursor-pointer">
                                            <input type="radio"
                                                   name="attendances[{{ $student->id }}][status]"
                                                   value="{{ $value }}"
                                                   x-model="statuses[{{ $student->id }}]"
                                                   @checked(old("attendances.{$student->id}.status", $record->status ?? null) === $value)
                                                   @if ($loop->first) required @endif
                                                   class="peer sr-only">
                                            <span class="status-option peer-focus-visible:ring-2"
                                                  :class="statuses[{{ $student->id }}] === '{{ $value }}' ? 'status-option-active status-option-{{ $value }}' : ''">
                                                {{ $label }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Catatan opsional --}}
                            <div x-cloak x-show="showNotes" class="mt-2.5">
                                <label for="note-{{ $student->id }}" class="sr-only">Catatan untuk {{ $student->user->name }}</label>
                                <input id="note-{{ $student->id }}"
                                       name="attendances[{{ $student->id }}][note]"
                                       type="text" maxlength="500"
                                       value="{{ old("attendances.{$student->id}.note", $record->note ?? '') }}"
                                       class="form-input !py-1.5 text-sm"
                                       placeholder="Catatan (opsional) — contoh: disertai surat dokter">
                            </div>

                            @error("attendances.{$student->id}.status")
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Aksi --}}
            <div class="mt-5 flex flex-wrap items-center justify-end gap-2">
                <a href="{{ route('guru.attendance.index', ['date' => $date->toDateString()]) }}" class="btn-outline">
                    Batal
                </a>

                <button type="submit" class="btn-primary">
                    <x-icon name="check" class="h-4 w-4" />
                    {{ $session ? 'Simpan Koreksi' : 'Simpan Absensi' }}
                </button>
            </div>
        </form>
    @endif
</x-layouts.app>
