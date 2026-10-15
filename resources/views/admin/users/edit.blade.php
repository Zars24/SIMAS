<x-layouts.app :title="'Ubah Akun: '.$user->name">
    <div class="mb-5">
        <a href="{{ route('admin.users.index') }}" class="btn-ghost btn-sm -ml-2">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke daftar
        </a>
        <p class="page-subtitle mt-2">Perbarui data akun dan statusnya.</p>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" novalidate>
        @csrf
        @method('PUT')
        @include('admin.users._form', ['user' => $user, 'submitLabel' => 'Simpan Perubahan'])
    </form>
</x-layouts.app>
