<x-layouts.app :title="'Tambah Siswa'">
    <div class="mb-5">
        <a href="{{ route('admin.students.index') }}" class="btn-ghost btn-sm -ml-2">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke daftar
        </a>
        <p class="page-subtitle mt-2">Buat akun login sekaligus profil siswa dalam satu langkah.</p>
    </div>

    <form method="POST" action="{{ route('admin.students.store') }}" novalidate>
        @csrf
        @include('admin.students._form', ['student' => $student, 'user' => $user, 'classes' => $classes, 'submitLabel' => 'Simpan Siswa'])
    </form>
</x-layouts.app>
