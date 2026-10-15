<x-layouts.app :title="'Ubah Siswa: '.$user->name">
    <div class="mb-5">
        <a href="{{ route('admin.students.index') }}" class="btn-ghost btn-sm -ml-2">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke daftar
        </a>
        <p class="page-subtitle mt-2">Perbarui akun dan profil siswa.</p>
    </div>

    <form method="POST" action="{{ route('admin.students.update', $student) }}" novalidate>
        @csrf
        @method('PUT')
        @include('admin.students._form', ['student' => $student, 'user' => $user, 'classes' => $classes, 'submitLabel' => 'Simpan Perubahan'])
    </form>
</x-layouts.app>
