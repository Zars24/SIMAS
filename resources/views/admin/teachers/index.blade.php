<x-layouts.app :title="'Guru'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <p class="page-subtitle">
            Data guru beserta akun login-nya. Guru hanya melihat jadwal dan kelas yang ditugaskan.
        </p>

        <a href="{{ route('admin.teachers.create') }}" class="btn-primary">
            <x-icon name="plus" class="h-4 w-4" />
            Tambah Guru
        </a>
    </div>

    <form method="GET" action="{{ route('admin.teachers.index') }}" class="card-pad mb-5">
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <label for="q" class="form-label">Cari</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-icon name="magnifying-glass" class="h-4 w-4" />
                    </span>
                    <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}"
                           class="form-input pl-10" placeholder="Nama, username, email, atau NIP…">
                </div>
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
            <a href="{{ route('admin.teachers.index') }}" class="btn-ghost btn-sm">Reset</a>
        </div>
    </form>

    <div class="table-wrap">
        @if ($teachers->isEmpty())
            <x-empty-state icon="briefcase"
                           title="Tidak ada guru ditemukan"
                           description="Tambahkan guru baru atau ubah filter pencarian.">
                <a href="{{ route('admin.teachers.create') }}" class="btn-primary btn-sm">
                    <x-icon name="plus" class="h-4 w-4" />
                    Tambah Guru
                </a>
            </x-empty-state>
        @else
            <table class="table-simas">
                <thead>
                    <tr>
                        <th scope="col">Guru</th>
                        <th scope="col">Kontak</th>
                        <th scope="col">Wali Kelas</th>
                        <th scope="col">Jadwal</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="!text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($teachers as $teacher)
                        <tr>
                            <td>
                                <p class="font-medium text-primary-900 dark:text-white">{{ $teacher->user->name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $teacher->teacher_number ? 'NIP '.$teacher->teacher_number : 'Tanpa NIP' }}
                                    · {{ '@'.$teacher->user->username }}
                                </p>
                            </td>
                            <td>
                                <p>{{ $teacher->user->email }}</p>
                                @if ($teacher->phone)
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $teacher->phone }}</p>
                                @endif
                            </td>
                            <td>
                                @forelse ($teacher->homeroomClasses as $class)
                                    <span class="badge-neutral">{{ $class->name }}</span>
                                @empty
                                    <span class="text-slate-400 dark:text-slate-500">—</span>
                                @endforelse
                            </td>
                            <td>
                                <span class="badge-slate">{{ $teacher->schedules_count }} jadwal</span>
                            </td>
                            <td>
                                @if ($teacher->user->is_active)
                                    <span class="badge-emerald">Aktif</span>
                                @else
                                    <span class="badge-slate">Nonaktif</span>
                                @endif
                            </td>
                            <td class="!text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn-ghost btn-sm"
                                       title="Ubah" aria-label="Ubah {{ $teacher->user->name }}">
                                        <x-icon name="pencil-square" class="h-4 w-4" />
                                    </a>

                                    <form method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}"
                                          data-confirm="Hapus data guru {{ $teacher->user->name }} beserta akunnya? Data dengan riwayat jadwal/absensi tidak akan terhapus."
                                          data-confirm-title="Hapus guru">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-ghost btn-sm !text-rose-600 hover:!bg-rose-50 dark:!text-rose-400 dark:hover:!bg-rose-500/10"
                                                title="Hapus" aria-label="Hapus {{ $teacher->user->name }}">
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
        {{ $teachers->links() }}
    </div>
</x-layouts.app>
