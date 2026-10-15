<x-layouts.app :title="'Tambah Jadwal'">
    <div class="mb-5">
        <a href="{{ route('admin.schedules.index') }}" class="btn-ghost btn-sm -ml-2">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke daftar
        </a>
        <p class="page-subtitle mt-2">Susun satu sesi jadwal mengajar untuk kelas tertentu.</p>
    </div>

    <form method="POST" action="{{ route('admin.schedules.store') }}" novalidate>
        @csrf
        @include('admin.schedules._form', ['schedule' => $schedule, 'options' => $options, 'submitLabel' => 'Simpan Jadwal'])
    </form>
</x-layouts.app>
