@props(['subject', 'submitLabel' => 'Simpan'])

<div class="grid gap-5 lg:grid-cols-3">
    <div class="space-y-5 lg:col-span-2">
        <div class="card-pad">
            <h2 class="section-title">Informasi Mata Pelajaran</h2>

            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="code" class="form-label">Kode <span class="req" aria-hidden="true">*</span></label>
                    <input id="code" name="code" type="text" required
                           value="{{ old('code', $subject->code) }}"
                           class="form-input font-mono uppercase @error('code') is-invalid @enderror" placeholder="Contoh: MAT-10">
                    <p class="form-hint">Kode unik, otomatis diubah menjadi huruf kapital.</p>
                    @error('code') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="name" class="form-label">Nama <span class="req" aria-hidden="true">*</span></label>
                    <input id="name" name="name" type="text" required
                           value="{{ old('name', $subject->name) }}"
                           class="form-input @error('name') is-invalid @enderror" placeholder="Contoh: Matematika">
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea id="description" name="description" rows="3"
                              class="form-textarea @error('description') is-invalid @enderror"
                              placeholder="Deskripsi singkat mata pelajaran (opsional)">{{ old('description', $subject->description) }}</textarea>
                    @error('description') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-5">
        <div class="card-pad">
            <h2 class="section-title">Ringkasan</h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Mata pelajaran yang masih dipakai pada jadwal tidak dapat dihapus. Hapus atau ubah jadwalnya
                terlebih dahulu agar riwayat absensi tetap utuh.
            </p>

            <div class="mt-4 flex flex-col gap-2">
                <button type="submit" class="btn-primary w-full">
                    <x-icon name="check" class="h-4 w-4" />
                    {{ $submitLabel }}
                </button>
                <a href="{{ route('admin.subjects.index') }}" class="btn-outline w-full">Batal</a>
            </div>
        </div>
    </div>
</div>
