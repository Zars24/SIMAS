<x-layouts.app :title="'Tambah Kelas'">
    <div class="mb-5">
        <a href="{{ route('admin.classes.index') }}" class="btn-ghost btn-sm -ml-2">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke daftar
        </a>
        <p class="page-subtitle mt-2">Buat kelas baru untuk satu tahun ajaran.</p>
    </div>

    <form method="POST" action="{{ route('admin.classes.store') }}" novalidate>
        @csrf
        @include('admin.classes._form', ['schoolClass' => $schoolClass, 'teachers' => $teachers, 'submitLabel' => 'Simpan Kelas'])
    </form>
</x-layouts.app>
