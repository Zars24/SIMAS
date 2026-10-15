<x-layouts.app :title="'Ubah Jadwal'">
    <div class="mb-5">
        <a href="{{ route('admin.schedules.index') }}" class="btn-ghost btn-sm -ml-2">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke daftar
        </a>
        <p class="page-subtitle mt-2">
            Perbarui jadwal: {{ $schedule->subject?->name }} — {{ $schedule->schoolClass?->name }}
            ({{ $schedule->dayLabel() }}, {{ $schedule->timeRange() }}).
        </p>
    </div>

    <form method="POST" action="{{ route('admin.schedules.update', $schedule) }}" novalidate>
        @csrf
        @method('PUT')
        @include('admin.schedules._form', ['schedule' => $schedule, 'options' => $options, 'submitLabel' => 'Simpan Perubahan'])
    </form>
</x-layouts.app>
