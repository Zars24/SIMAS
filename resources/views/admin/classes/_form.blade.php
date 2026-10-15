@props(['schoolClass', 'teachers', 'submitLabel' => 'Simpan'])

<div class="grid gap-5 lg:grid-cols-3">
    <div class="space-y-5 lg:col-span-2">
        <div class="card-pad">
            <h2 class="section-title">Informasi Kelas</h2>

            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="form-label">Nama Kelas <span class="req" aria-hidden="true">*</span></label>
                    <input id="name" name="name" type="text" value="{{ old('name', $schoolClass->name) }}" required
                           class="form-input @error('name') is-invalid @enderror" placeholder="Contoh: X IPA 1">
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="academic_year" class="form-label">Tahun Ajaran <span class="req" aria-hidden="true">*</span></label>
                    <input id="academic_year" name="academic_year" type="text" required
                           value="{{ old('academic_year', $schoolClass->academic_year) }}"
                           class="form-input @error('academic_year') is-invalid @enderror" placeholder="Contoh: 2025/2026">
                    <p class="form-hint">Format YYYY/YYYY.</p>
                    @error('academic_year') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="homeroom_teacher_id" class="form-label">Wali Kelas</label>
                    <select id="homeroom_teacher_id" name="homeroom_teacher_id"
                            class="form-select @error('homeroom_teacher_id') is-invalid @enderror">
                        <option value="">Tanpa wali kelas</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected((string) old('homeroom_teacher_id', $schoolClass->homeroom_teacher_id) === (string) $teacher->id)>
                                {{ $teacher->user->name }}{{ $teacher->teacher_number ? ' · '.$teacher->teacher_number : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="form-hint">Opsional. Satu guru hanya boleh menjadi wali satu kelas pada tahun ajaran yang sama.</p>
                    @error('homeroom_teacher_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <span class="form-label">Status Kelas</span>
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-primary-200 bg-white px-3.5 py-2.5 shadow-sm dark:border-primary-700 dark:bg-primary-950/60">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $schoolClass->exists ? $schoolClass->is_active : true))
                               class="form-checkbox h-4 w-4">
                        <span class="text-sm text-slate-700 dark:text-slate-300">Aktif — kelas muncul saat penjadwalan dan pengisian absensi</span>
                    </label>
                    @error('is_active') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-5">
        <div class="card-pad">
            <h2 class="section-title">Ringkasan</h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Setelah kelas dibuat, tempatkan siswa dari menu <span class="font-medium">Siswa</span> dan susun jadwalnya
                dari menu <span class="font-medium">Jadwal</span>.
            </p>

            <div class="mt-4 flex flex-col gap-2">
                <button type="submit" class="btn-primary w-full">
                    <x-icon name="check" class="h-4 w-4" />
                    {{ $submitLabel }}
                </button>
                <a href="{{ route('admin.classes.index') }}" class="btn-outline w-full">Batal</a>
            </div>
        </div>
    </div>
</div>
