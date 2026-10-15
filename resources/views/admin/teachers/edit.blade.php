<x-layouts.app :title="'Ubah Guru: '.$user->name">
    <div class="mb-5">
        <a href="{{ route('admin.teachers.index') }}" class="btn-ghost btn-sm -ml-2">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke daftar
        </a>
        <p class="page-subtitle mt-2">Perbarui akun dan profil guru.</p>
    </div>

    <form method="POST" action="{{ route('admin.teachers.update', $teacher) }}" novalidate>
        @csrf
        @method('PUT')
        @include('admin.teachers._form', ['teacher' => $teacher, 'user' => $user, 'submitLabel' => 'Simpan Perubahan'])
    </form>
</x-layouts.app>
