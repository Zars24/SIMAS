<x-layouts.app :title="'Pengguna'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <p class="page-subtitle">
            Kelola akun login untuk admin, guru, dan siswa. Role menentukan hak akses di dalam aplikasi.
        </p>

        <a href="{{ route('admin.users.create') }}" class="btn-primary">
            <x-icon name="plus" class="h-4 w-4" />
            Tambah Akun
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="card-pad mb-5">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <label for="q" class="form-label">Cari</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-icon name="magnifying-glass" class="h-4 w-4" />
                    </span>
                    <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}"
                           class="form-input pl-10" placeholder="Nama, username, atau email…">
                </div>
            </div>

            <div>
                <label for="role" class="form-label">Role</label>
                <select id="role" name="role" class="form-select">
                    <option value="">Semua role</option>
                    @foreach (\App\Models\User::ROLES as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['role'] ?? '') === $value)>{{ $label }}</option>
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
            <a href="{{ route('admin.users.index') }}" class="btn-ghost btn-sm">Reset</a>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="table-wrap">
        @if ($users->isEmpty())
            <x-empty-state icon="users"
                           title="Tidak ada akun ditemukan"
                           description="Coba ubah kata kunci filter, atau tambahkan akun baru.">
                <a href="{{ route('admin.users.create') }}" class="btn-primary btn-sm">
                    <x-icon name="plus" class="h-4 w-4" />
                    Tambah Akun
                </a>
            </x-empty-state>
        @else
            <table class="table-simas">
                <thead>
                    <tr>
                        <th scope="col">Nama</th>
                        <th scope="col">Email</th>
                        <th scope="col">Role</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="!text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700 dark:bg-primary-500/15 dark:text-primary-200">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-primary-900 dark:text-white">{{ $user->name }}</p>
                                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ '@'.$user->username }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="{{ $user->isAdmin() ? 'badge-gold' : 'badge-neutral' }}">{{ $user->roleLabel() }}</span>

                                @if ($user->isPrimaryAdmin())
                                    <span class="badge-emerald ml-1">Utama</span>
                                @endif
                            </td>
                            <td>
                                @if ($user->is_active)
                                    <span class="badge bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-400/30">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="badge bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-400/30">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>
                                        Nonaktif
                                    </span>
                                @endif

                                @if ($user->isGuru() && ! $user->teacher)
                                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">Profil guru belum dibuat</p>
                                @elseif ($user->isSiswa() && ! $user->student)
                                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">Profil siswa belum dibuat</p>
                                @endif
                            </td>
                            <td class="!text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn-ghost btn-sm" title="Detail" aria-label="Detail {{ $user->name }}">
                                        <x-icon name="eye" class="h-4 w-4" />
                                    </a>

                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn-ghost btn-sm" title="Ubah" aria-label="Ubah {{ $user->name }}">
                                        <x-icon name="pencil-square" class="h-4 w-4" />
                                    </a>

                                    <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}"
                                          data-confirm="{{ $user->is_active
                                              ? 'Nonaktifkan akun '.$user->name.'? Pengguna tidak akan bisa masuk sampai diaktifkan kembali.'
                                              : 'Aktifkan kembali akun '.$user->name.'?' }}"
                                          data-confirm-title="{{ $user->is_active ? 'Nonaktifkan akun' : 'Aktifkan akun' }}"
                                          data-confirm-label="{{ $user->is_active ? 'Ya, nonaktifkan' : 'Ya, aktifkan' }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-ghost btn-sm" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                aria-label="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ $user->name }}">
                                            <x-icon name="arrow-path" class="h-4 w-4" />
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                          data-confirm="Hapus akun {{ $user->name }}? Tindakan ini tidak dapat dibatalkan."
                                          data-confirm-title="Hapus akun">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-ghost btn-sm !text-rose-600 hover:!bg-rose-50 dark:!text-rose-400 dark:hover:!bg-rose-500/10"
                                                title="Hapus" aria-label="Hapus {{ $user->name }}">
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
        {{ $users->links() }}
    </div>
</x-layouts.app>
