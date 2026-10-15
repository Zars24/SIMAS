<x-layouts.app :title="'Tambah Akun Pengguna'">
    <div class="mb-5">
        <a href="{{ route('admin.users.index') }}" class="btn-ghost btn-sm -ml-2">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke daftar
        </a>
        <p class="page-subtitle mt-2">Buat akun baru untuk admin, guru, atau siswa.</p>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}" novalidate>
        @csrf
        @include('admin.users._form', ['user' => $user, 'submitLabel' => 'Simpan Akun'])
    </form>
</x-layouts.app>
