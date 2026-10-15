<x-layouts.app :title="'Kelas'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <p class="page-subtitle">
            Daftar kelas beserta wali kelas dan jumlah siswanya. Satu guru hanya boleh menjadi wali satu kelas per tahun ajaran.
        </p>

        <a href="{{ route('admin.classes.create') }}" class="btn-primary">
            <x-icon name="plus" class="h-4 w-4" />
            Tambah Kelas
        </a>
    </div>

    <form method="GET" action="{{ route('admin.classes.index') }}" class="card-pad mb-5">
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label for="q" class="form-label">Cari</label>
                <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}"
                       class="form-input" placeholder="Nama kelas…">
            </div>

            <div>
                <label for="academic_year" class="form-label">Tahun Ajaran</label>
                <select id="academic_year" name="academic_year" class="form-select">
                    <option value="">Semua tahun ajaran</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year }}" @selected(($filters['academic_year'] ?? '') === $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Semua status</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
                </select>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button type="submit" class="btn-primary btn-sm">
                <x-icon name="funnel" class="h-4 w-4" />
                Terapkan Filter
            </button>
            <a href="{{ route('admin.classes.index') }}" class="btn-ghost btn-sm">Reset</a>
        </div>
    </form>

    <div class="table-wrap">
        @if ($classes->isEmpty())
            <x-empty-state icon="building-library"
                           title="Tidak ada kelas ditemukan"
                           description="Tambahkan kelas baru atau ubah filter pencarian.">
                <a href="{{ route('admin.classes.create') }}" class="btn-primary btn-sm">
                    <x-icon name="plus" class="h-4 w-4" />
                    Tambah Kelas
                </a>
            </x-empty-state>
        @else
            <table class="table-simas">
                <thead>
                    <tr>
                        <th scope="col">Kelas</th>
                        <th scope="col">Wali Kelas</th>
                        <th scope="col">Siswa</th>
                        <th scope="col">Jadwal</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="!text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($classes as $class)
                        <tr>
                            <td>
                                <p class="font-medium text-primary-900 dark:text-white">{{ $class->name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Tahun ajaran {{ $class->academic_year }}</p>
                            </td>
                            <td>
                                {{ $class->homeroomTeacher?->user?->name ?? '—' }}
                            </td>
                            <td>
                                <a href="{{ route('admin.students.index', ['class_id' => $class->id]) }}"
                                   class="inline-flex items-center gap-1 font-medium text-primary-600 hover:text-primary-700 dark:text-primary-300">
                                    {{ $class->students_count }} siswa
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.schedules.index', ['class_id' => $class->id]) }}"
                                   class="inline-flex items-center gap-1 font-medium text-primary-600 hover:text-primary-700 dark:text-primary-300">
                                    {{ $class->schedules_count }} jadwal
                                </a>
                            </td>
                            <td>
                                @if ($class->is_active)
                                    <span class="badge-emerald">Aktif</span>
                                @else
                                    <span class="badge-slate">Nonaktif</span>
                                @endif
                            </td>
                            <td class="!text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.classes.edit', $class) }}" class="btn-ghost btn-sm"
                                       title="Ubah" aria-label="Ubah kelas {{ $class->name }}">
                                        <x-icon name="pencil-square" class="h-4 w-4" />
                                    </a>

                                    <form method="POST" action="{{ route('admin.classes.destroy', $class) }}"
                                          data-confirm="Hapus kelas {{ $class->displayName() }}? Kelas yang masih memiliki siswa atau jadwal tidak akan terhapus."
                                          data-confirm-title="Hapus kelas">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-ghost btn-sm !text-rose-600 hover:!bg-rose-50 dark:!text-rose-400 dark:hover:!bg-rose-500/10"
                                                title="Hapus" aria-label="Hapus kelas {{ $class->name }}">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="mt-5">
        {{ $classes->links() }}
    </div>
</x-layouts.app>
