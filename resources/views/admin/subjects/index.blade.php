<x-layouts.app :title="'Mata Pelajaran'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <p class="page-subtitle">
            Daftar mata pelajaran yang dipakai pada penyusunan jadwal.
        </p>

        <a href="{{ route('admin.subjects.create') }}" class="btn-primary">
            <x-icon name="plus" class="h-4 w-4" />
            Tambah Mata Pelajaran
        </a>
    </div>

    <form method="GET" action="{{ route('admin.subjects.index') }}" class="card-pad mb-5">
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <label for="q" class="form-label">Cari</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-icon name="magnifying-glass" class="h-4 w-4" />
                    </span>
                    <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}"
                           class="form-input pl-10" placeholder="Nama atau kode mata pelajaran…">
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button type="submit" class="btn-primary btn-sm">
                <x-icon name="funnel" class="h-4 w-4" />
                Terapkan Filter
            </button>
            <a href="{{ route('admin.subjects.index') }}" class="btn-ghost btn-sm">Reset</a>
        </div>
    </form>

    <div class="table-wrap">
        @if ($subjects->isEmpty())
            <x-empty-state icon="book-open"
                           title="Tidak ada mata pelajaran ditemukan"
                           description="Tambahkan mata pelajaran baru atau ubah kata kunci pencarian.">
                <a href="{{ route('admin.subjects.create') }}" class="btn-primary btn-sm">
                    <x-icon name="plus" class="h-4 w-4" />
                    Tambah Mata Pelajaran
                </a>
            </x-empty-state>
        @else
            <table class="table-simas">
                <thead>
                    <tr>
                        <th scope="col">Kode</th>
                        <th scope="col">Nama</th>
                        <th scope="col">Deskripsi</th>
                        <th scope="col">Dipakai di Jadwal</th>
                        <th scope="col" class="!text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subjects as $subject)
                        <tr>
                            <td>
                                <span class="badge-neutral font-mono">{{ $subject->code }}</span>
                            </td>
                            <td>
                                <p class="font-medium text-primary-900 dark:text-white">{{ $subject->name }}</p>
                            </td>
                            <td class="max-w-[22rem]">
                                <span class="line-clamp-2 text-sm text-slate-600 dark:text-slate-300">{{ $subject->description ?? '—' }}</span>
                            </td>
                            <td>
                                @if ($subject->schedules_count > 0)
                                    <a href="{{ route('admin.schedules.index', ['subject_id' => $subject->id]) }}"
                                       class="inline-flex items-center gap-1 font-medium text-primary-600 hover:text-primary-700 dark:text-primary-300">
                                        {{ $subject->schedules_count }} jadwal
                                    </a>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">Belum dipakai</span>
                                @endif
                            </td>
                            <td class="!text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn-ghost btn-sm"
                                       title="Ubah" aria-label="Ubah {{ $subject->name }}">
                                        <x-icon name="pencil-square" class="h-4 w-4" />
                                    </a>

                                    <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}"
                                          data-confirm="Hapus mata pelajaran {{ $subject->name }}? Mata pelajaran yang masih dipakai pada jadwal tidak akan terhapus."
                                          data-confirm-title="Hapus mata pelajaran">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-ghost btn-sm !text-rose-600 hover:!bg-rose-50 dark:!text-rose-400 dark:hover:!bg-rose-500/10"
                                                title="Hapus" aria-label="Hapus {{ $subject->name }}">
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
        {{ $subjects->links() }}
    </div>
</x-layouts.app>
