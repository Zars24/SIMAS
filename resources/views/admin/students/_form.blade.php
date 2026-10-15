@props(['student', 'user', 'classes', 'submitLabel' => 'Simpan'])

<div class="grid gap-5 lg:grid-cols-3">
    <div class="space-y-5 lg:col-span-2">
        <div class="card-pad">
            <h2 class="section-title">Akun Login</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Akun ini dipakai siswa untuk masuk ke SIMAS.</p>

            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="form-label">Nama Lengkap <span class="req" aria-hidden="true">*</span></label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                           class="form-input @error('name') is-invalid @enderror" @error('name') aria-invalid="true" @enderror>
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="username" class="form-label">Username <span class="req" aria-hidden="true">*</span></label>
                    <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" required
                           class="form-input @error('username') is-invalid @enderror" @error('username') aria-invalid="true" @enderror>
                    <p class="form-hint">Huruf, angka, tanda minus, dan garis bawah.</p>
                    @error('username') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="form-label">Email <span class="req" aria-hidden="true">*</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                           class="form-input @error('email') is-invalid @enderror" @error('email') aria-invalid="true" @enderror>
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="form-label">
                        Kata Sandi @if (! $user->exists) <span class="req" aria-hidden="true">*</span> @endif
                    </label>
                    <input id="password" name="password" type="text" autocomplete="new-password"
                           class="form-input @error('password') is-invalid @enderror"
                           placeholder="{{ $user->exists ? 'Kosongkan bila tidak diganti' : 'Minimal 8 karakter' }}">
                    <p class="form-hint">Minimal 8 karakter, tersimpan dalam bentuk hash.</p>
                    @error('password') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <span class="form-label">Status Akun</span>
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-primary-200 bg-white px-3.5 py-2.5 shadow-sm dark:border-primary-700 dark:bg-primary-950/60">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->exists ? $user->is_active : true))
                               class="form-checkbox h-4 w-4">
                        <span class="text-sm text-slate-700 dark:text-slate-300">Aktif — siswa dapat masuk dan melihat data akademiknya</span>
                    </label>
                    @error('is_active') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="card-pad">
            <h2 class="section-title">Profil Siswa</h2>

            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="student_number" class="form-label">Nomor Induk Siswa (NIS) <span class="req" aria-hidden="true">*</span></label>
                    <input id="student_number" name="student_number" type="text" required
                           value="{{ old('student_number', $student->student_number) }}"
                           class="form-input @error('student_number') is-invalid @enderror" placeholder="Contoh: S-2024001">
                    @error('student_number') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="class_id" class="form-label">Kelas</label>
                    <select id="class_id" name="class_id" class="form-select @error('class_id') is-invalid @enderror">
                        <option value="">Belum ditempatkan</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected((string) old('class_id', $student->class_id) === (string) $class->id)>
                                {{ $class->displayName() }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="birth_date" class="form-label">Tanggal Lahir</label>
                    <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date', $student->birth_date?->format('Y-m-d')) }}"
                           class="form-input @error('birth_date') is-invalid @enderror">
                    @error('birth_date') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="form-label">Nomor Telepon</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $student->phone) }}"
                           class="form-input @error('phone') is-invalid @enderror" placeholder="Nomor siswa atau orang tua (opsional)">
                    @error('phone') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="address" class="form-label">Alamat</label>
                    <textarea id="address" name="address" rows="2"
                              class="form-textarea @error('address') is-invalid @enderror"
                              placeholder="Alamat domisili (opsional)">{{ old('address', $student->address) }}</textarea>
                    @error('address') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-5">
        <div class="card-pad">
            <h2 class="section-title">Ringkasan</h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Siswa hanya melihat jadwal kelasnya dan riwayat absensinya sendiri — tidak dapat melihat data siswa lain.
                Riwayat absensi tetap tersimpan ketika siswa dinonaktifkan.
            </p>

            <div class="mt-4 flex flex-col gap-2">
                <button type="submit" class="btn-primary w-full">
                    <x-icon name="check" class="h-4 w-4" />
                    {{ $submitLabel }}
                </button>
                <a href="{{ route('admin.students.index') }}" class="btn-outline w-full">Batal</a>
            </div>
        </div>
    </div>
</div>
