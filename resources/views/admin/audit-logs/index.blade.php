<x-layouts.app :title="'Audit Log'">
    <div class="mb-5">
        <p class="page-subtitle">
            Riwayat tindakan penting di dalam sistem: pembuatan data, perubahan, penghapusan, dan aktivitas absensi.
        </p>
    </div>

    <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="card-pad mb-5">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="q" class="form-label">Pelaku</label>
                <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}"
                       class="form-input" placeholder="Nama pengguna…">
            </div>

            <div>
                <label for="action" class="form-label">Aksi</label>
                <select id="action" name="action" class="form-select">
                    <option value="">Semua aksi</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="date_from" class="form-label">Dari Tanggal</label>
                <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}" class="form-input">
            </div>

            <div>
                <label for="date_to" class="form-label">Sampai Tanggal</label>
                <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}" class="form-input">
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button type="submit" class="btn-primary btn-sm">
                <x-icon name="funnel" class="h-4 w-4" />
                Terapkan Filter
            </button>
            <a href="{{ route('admin.audit-logs.index') }}" class="btn-ghost btn-sm">Reset</a>
        </div>
    </form>

    <div class="table-wrap">
        @if ($logs->isEmpty())
            <x-empty-state icon="document-text"
                           title="Tidak ada catatan audit"
                           description="Belum ada aktivitas yang tercatat pada filter ini." />
        @else
            <table class="table-simas">
                <thead>
                    <tr>
                        <th scope="col">Waktu</th>
                        <th scope="col">Pelaku</th>
                        <th scope="col">Aksi</th>
                        <th scope="col">Objek</th>
                        <th scope="col">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td class="whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">
                                @tanggaljam($log->created_at)
                            </td>
                            <td>
                                @if ($log->user)
                                    <p class="font-medium text-primary-900 dark:text-white">{{ $log->user->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $log->user->roleLabel() }}</p>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">Sistem</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-neutral font-mono text-xs">{{ $log->action }}</span>
                            </td>
                            <td class="text-xs text-slate-500 dark:text-slate-400">
                                @if ($log->entity_type)
                                    {{ $log->entityLabel() }}
                                    @if ($log->entity_id) #{{ $log->entity_id }} @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="max-w-[24rem]">
                                @if (! empty($log->metadata))
                                    <code class="block truncate rounded-lg bg-primary-50 px-2 py-1 text-xs text-primary-800 dark:bg-primary-900/60 dark:text-primary-200"
                                          title="{{ json_encode($log->metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}">
                                        {{ json_encode($log->metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}
                                    </code>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="mt-5">
        {{ $logs->links() }}
    </div>
</x-layouts.app>
