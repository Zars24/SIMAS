<x-layouts.app :title="'Ubah Mata Pelajaran: '.$subject->name">
    <div class="mb-5">
        <a href="{{ route('admin.subjects.index') }}" class="btn-ghost btn-sm -ml-2">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke daftar
        </a>
        <p class="page-subtitle mt-2">Perbarui informasi mata pelajaran.</p>
    </div>

    <form method="POST" action="{{ route('admin.subjects.update', $subject) }}" novalidate>
        @csrf
        @method('PUT')
        @include('admin.subjects._form', ['subject' => $subject, 'submitLabel' => 'Simpan Perubahan'])
    </form>
</x-layouts.app>
