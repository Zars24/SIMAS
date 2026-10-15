<x-layouts.app :title="'Jadwal Pelajaran'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <p class="page-subtitle">
            Jadwal mengajar per kelas. Guru hanya dapat mengisi absensi pada jadwal yang ditugaskan kepadanya.
        </p>

        <a href="{{ route('admin.schedules.create', request()->only('class_id')) }}" class="btn-primary">
            <x-icon name="plus" class="h-4 w-4" />
            Tambah Jadwal
        </a>
    </div>

    <form method="GET" action="{{ route('admin.schedules.index') }}" class="card-pad mb-5">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label for="class_id" class="form-label">Kelas</label>
                <select id="class_id" name="class_id" class="form-select">
                    <option value="">Semua kelas</option>
                    @foreach ($options['classes'] as $class)
                        <option value="{{ $class->id }}" @selected((string) ($filters['class_id'] ?? '') === (string) $class->id)>
                            {{ $class->displayName() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="teacher_id" class="form-label">Guru</label>
                <select id="teacher_id" name="teacher_id" class="form-select">
                    <option value="">Semua guru</option>
                    @foreach ($options['teachers'] as $teacher)
                        <option value="{{ $teacher->id }}" @selected((string) ($filters['teacher_id'] ?? '') === (string) $teacher->id)>
                            {{ $teacher->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="subject_id" class="form-label">Mata Pelajaran</label>
                <select id="subject_id" name="subject_id" class="form-select">
                    <option value="">Semua mapel</option>
                    @foreach ($options['subjects'] as $subject)
                        <option value="{{ $subject->id }}" @selected((string) ($filters['subject_id'] ?? '') === (string) $subject->id)>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="day" class="form-label">Hari</label>
                <select id="day" name="day" class="form-select">
                    <option value="">Semua hari</option>
                    @foreach ($options['days'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['day'] ?? '') === $value)>{{ $label }}</option>
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
            <a href="{{ route('admin.schedules.index') }}" class="btn-ghost btn-sm">Reset</a>
        </div>
    </form>

    <div class="table-wrap">
        @if ($schedules->isEmpty())
            <x-empty-state icon="calendar-days"
                           title="Tidak ada jadwal ditemukan"
                           description="Tambahkan jadwal baru atau ubah filter pencarian.">
                <a href="{{ route('admin.schedules.create') }}" class="btn-primary btn-sm">
                    <x-icon name="plus" class="h-4 w-4" />
                    Tambah Jadwal
                </a>
            </x-empty-state>
        @else
            <table class="table-simas">
                <thead>
                    <tr>
                        <th scope="col">Hari &amp; Jam</th>
                        <th scope="col">Kelas</th>
                        <th scope="col">Mata Pelajaran</th>
                        <th scope="col">Guru</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="!text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($schedules as $schedule)
                        <tr>
                            <td>
                                <p class="font-medium text-primary-900 dark:text-white">{{ $schedule->dayLabel() }}</p>
                                <p class="text-xs tabular-nums text-slate-500 dark:text-slate-400">{{ $schedule->timeRange() }}</p>
                            </td>
                            <td>{{ $schedule->schoolClass?->name ?? '—' }}</td>
                            <td>
                                <p>{{ $schedule->subject?->name ?? '—' }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $schedule->subject?->code }}</p>
                            </td>
                            <td>{{ $schedule->teacher?->user?->name ?? '—' }}</td>
                            <td>
                                @if ($schedule->is_active)
                                    <span class="badge-emerald">Aktif</span>
                                @else
                                    <span class="badge-slate">Nonaktif</span>
                                @endif
                            </td>
                            <td class="!text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn-ghost btn-sm"
                                       title="Ubah" aria-label="Ubah jadwal {{ $schedule->dayLabel() }} {{ $schedule->timeRange() }}">
                                        <x-icon name="pencil-square" class="h-4 w-4" />
                                    </a>

                                    <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}"
                                          data-confirm="Hapus jadwal {{ $schedule->subject?->name }} — {{ $schedule->schoolClass?->name }} ({{ $schedule->dayLabel() }}, {{ $schedule->timeRange() }})? Jadwal dengan riwayat absensi tidak akan terhapus."
                                          data-confirm-title="Hapus jadwal">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-ghost btn-sm !text-rose-600 hover:!bg-rose-50 dark:!text-rose-400 dark:hover:!bg-rose-500/10"
                                                title="Hapus" aria-label="Hapus jadwal">
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
        {{ $schedules->links() }}
    </div>
</x-layouts.app>
