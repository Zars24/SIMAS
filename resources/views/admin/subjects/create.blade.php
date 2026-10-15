<x-layouts.app :title="'Tambah Mata Pelajaran'">
    <div class="mb-5">
        <a href="{{ route('admin.subjects.index') }}" class="btn-ghost btn-sm -ml-2">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke daftar
        </a>
        <p class="page-subtitle mt-2">Tambahkan satu mata pelajaran ke kurikulum sekolah.</p>
    </div>

    <form method="POST" action="{{ route('admin.subjects.store') }}" novalidate>
        @csrf
        @include('admin.subjects._form', ['subject' => $subject, 'submitLabel' => 'Simpan Mata Pelajaran'])
    </form>
</x-layouts.app>
