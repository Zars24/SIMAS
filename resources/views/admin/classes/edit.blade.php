<x-layouts.app :title="'Ubah Kelas: '.$schoolClass->displayName()">
    <div class="mb-5">
        <a href="{{ route('admin.classes.index') }}" class="btn-ghost btn-sm -ml-2">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke daftar
        </a>
        <p class="page-subtitle mt-2">Perbarui informasi kelas dan wali kelas.</p>
    </div>

    <form method="POST" action="{{ route('admin.classes.update', $schoolClass) }}" novalidate>
        @csrf
        @method('PUT')
        @include('admin.classes._form', ['schoolClass' => $schoolClass, 'teachers' => $teachers, 'submitLabel' => 'Simpan Perubahan'])
    </form>
</x-layouts.app>
