@props(['schedule', 'options', 'submitLabel' => 'Simpan'])

@php
    $startValue = old('start_time', $schedule->start_time ? \Illuminate\Support\Str::substr((string) $schedule->start_time, 0, 5) : '');
    $endValue = old('end_time', $schedule->end_time ? \Illuminate\Support\Str::substr((string) $schedule->end_time, 0, 5) : '');
@endphp

<div class="grid gap-5 lg:grid-cols-3">
    <div class="space-y-5 lg:col-span-2">
        <div class="card-pad">
            <h2 class="section-title">Informasi Jadwal</h2>

            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="class_id" class="form-label">Kelas <span class="req" aria-hidden="true">*</span></label>
                    <select id="class_id" name="class_id" required class="form-select @error('class_id') is-invalid @enderror">
                        <option value="">Pilih kelas…</option>
                        @foreach ($options['classes'] as $class)
                            <option value="{{ $class->id }}" @selected((string) old('class_id', $schedule->class_id) === (string) $class->id)>
                                {{ $class->displayName() }}{{ $class->is_active ? '' : ' (nonaktif)' }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="subject_id" class="form-label">Mata Pelajaran <span class="req" aria-hidden="true">*</span></label>
                    <select id="subject_id" name="subject_id" required class="form-select @error('subject_id') is-invalid @enderror">
                        <option value="">Pilih mata pelajaran…</option>
                        @foreach ($options['subjects'] as $subject)
                            <option value="{{ $subject->id }}" @selected((string) old('subject_id', $schedule->subject_id) === (string) $subject->id)>
                                {{ $subject->name }} ({{ $subject->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="teacher_id" class="form-label">Guru Pengampu <span class="req" aria-hidden="true">*</span></label>
                    <select id="teacher_id" name="teacher_id" required class="form-select @error('teacher_id') is-invalid @enderror">
                        <option value="">Pilih guru…</option>
                        @foreach ($options['teachers'] as $teacher)
                            <option value="{{ $teacher->id }}" @selected((string) old('teacher_id', $schedule->teacher_id) === (string) $teacher->id)>
                                {{ $teacher->user->name }}{{ $teacher->teacher_number ? ' · '.$teacher->teacher_number : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="form-hint">Guru ini yang akan melihat jadwal dan mengisi absensi pada sesi terkait.</p>
                    @error('teacher_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="day_of_week" class="form-label">Hari <span class="req" aria-hidden="true">*</span></label>
                    <select id="day_of_week" name="day_of_week" required class="form-select @error('day_of_week') is-invalid @enderror">
                        @foreach ($options['days'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('day_of_week', $schedule->day_of_week) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('day_of_week') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div></div>

                <div>
                    <label for="start_time" class="form-label">Jam Mulai <span class="req" aria-hidden="true">*</span></label>
                    <input id="start_time" name="start_time" type="time" required value="{{ $startValue }}"
                           class="form-input tabular-nums @error('start_time') is-invalid @enderror">
                    @error('start_time') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="end_time" class="form-label">Jam Selesai <span class="req" aria-hidden="true">*</span></label>
                    <input id="end_time" name="end_time" type="time" required value="{{ $endValue }}"
                           class="form-input tabular-nums @error('end_time') is-invalid @enderror">
                    @error('end_time') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <span class="form-label">Status Jadwal</span>
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-primary-200 bg-white px-3.5 py-2.5 shadow-sm dark:border-primary-700 dark:bg-primary-950/60">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $schedule->exists ? $schedule->is_active : true))
                               class="form-checkbox h-4 w-4">
                        <span class="text-sm text-slate-700 dark:text-slate-300">Aktif — jadwal muncul pada menu guru dan pengisian absensi</span>
                    </label>
                    @error('is_active') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-5">
        <div class="card-pad">
            <h2 class="section-title">Catatan Bentrok</h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Sistem menolak jadwal yang bentrok: guru mengajar dua kelas pada waktu yang sama,
                atau kelas punya dua mata pelajaran pada jam yang sama.
            </p>

            <div class="mt-4 flex flex-col gap-2">
                <button type="submit" class="btn-primary w-full">
                    <x-icon name="check" class="h-4 w-4" />
                    {{ $submitLabel }}
                </button>
                <a href="{{ route('admin.schedules.index') }}" class="btn-outline w-full">Batal</a>
            </div>
        </div>
    </div>
</div>
