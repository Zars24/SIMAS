@props(['user', 'submitLabel' => 'Simpan'])

@php
    $hasProfile = $user->exists && ($user->teacher()->exists() || $user->student()->exists());
@endphp

<div class="grid gap-5 lg:grid-cols-3">
    <div class="space-y-5 lg:col-span-2">
        <div class="card-pad">
            <h2 class="section-title">Informasi Akun</h2>

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
                    <label for="role" class="form-label">Role <span class="req" aria-hidden="true">*</span></label>
                    <select id="role" name="role" required class="form-select @error('role') is-invalid @enderror"
                            @error('role') aria-invalid="true" @enderror>
                        @foreach (\App\Models\User::ROLES as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @if ($hasProfile)
                        <p class="form-hint">Role akun dengan profil guru/siswa tidak dapat diubah.</p>
                    @endif
                    @error('role') <p class="form-error">{{ $message }}</p> @enderror
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

                <div>
                    <span class="form-label">Status Akun</span>
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-primary-200 bg-white px-3.5 py-2.5 shadow-sm dark:border-primary-700 dark:bg-primary-950/60">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->exists ? $user->is_active : true))
                               class="form-checkbox h-4 w-4">
                        <span class="text-sm text-slate-700 dark:text-slate-300">Aktif — akun dapat digunakan untuk masuk</span>
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
                Role menentukan menu dan hak akses setelah masuk. Akun nonaktif akan ditolak saat login
                dan langsung dikeluarkan bila dinonaktifkan saat sesi berjalan.
            </p>

            <div class="mt-4 flex flex-col gap-2">
                <button type="submit" class="btn-primary w-full">
                    <x-icon name="check" class="h-4 w-4" />
                    {{ $submitLabel }}
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn-outline w-full">Batal</a>
            </div>
        </div>
    </div>
</div>
