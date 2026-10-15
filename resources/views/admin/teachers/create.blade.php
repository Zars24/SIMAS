<x-layouts.app :title="'Tambah Guru'">
    <div class="mb-5">
        <a href="{{ route('admin.teachers.index') }}" class="btn-ghost btn-sm -ml-2">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke daftar
        </a>
        <p class="page-subtitle mt-2">Buat akun login sekaligus profil guru dalam satu langkah.</p>
    </div>

    <form method="POST" action="{{ route('admin.teachers.store') }}" novalidate>
        @csrf
        @include('admin.teachers._form', ['teacher' => $teacher, 'user' => $user, 'submitLabel' => 'Simpan Guru'])
    </form>
</x-layouts.app>
