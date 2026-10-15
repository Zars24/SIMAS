<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with(['teacher:id,user_id', 'student:id,user_id'])
            ->search($request->string('q')->toString())
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status')->toString() === 'active'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $request->only(['q', 'role', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'user' => new User(['role' => User::ROLE_SISWA, 'is_active' => true]),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create($request->validated());

        AuditLogger::log('user.created', $user, ['role' => $user->role]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun {$user->name} berhasil dibuat.");
    }

    public function show(User $user): View
    {
        $user->load(['teacher.homeroomClasses.schoolClass', 'student.schoolClass']);

        return view('admin.users.show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if ($user->isPrimaryAdmin() && ($data['role'] !== $user->role || ! $data['is_active'])) {
            return back()->withInput()->withErrors([
                'role' => 'Akun admin utama tidak dapat dinonaktifkan atau diubah role-nya.',
            ]);
        }

        if ($user->is($request->user()) && ! $data['is_active']) {
            return back()->withInput()->withErrors([
                'is_active' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.',
            ]);
        }

        if ($data['role'] !== $user->role && ($user->teacher()->exists() || $user->student()->exists())) {
            return back()->withInput()->withErrors([
                'role' => 'Role tidak dapat diubah karena akun ini memiliki profil guru/siswa.',
            ]);
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        AuditLogger::log('user.updated', $user, ['role' => $user->role, 'is_active' => $user->is_active]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun {$user->name} berhasil diperbarui.");
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        if ($user->isPrimaryAdmin()) {
            return back()->with('error', 'Akun admin utama tidak dapat dinonaktifkan.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        AuditLogger::log($user->is_active ? 'user.activated' : 'user.deactivated', $user);

        return back()->with(
            'success',
            $user->is_active
                ? "Akun {$user->name} diaktifkan kembali."
                : "Akun {$user->name} dinonaktifkan."
        );
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ], [
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        $user->update(['password' => $validated['password']]);

        AuditLogger::log('user.password_reset', $user);

        return back()->with('success', "Kata sandi {$user->name} berhasil direset.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        if ($user->isPrimaryAdmin()) {
            return back()->with('error', 'Akun admin utama tidak dapat dihapus.');
        }

        if ($user->teacher()->exists() || $user->student()->exists()) {
            return back()->with('error', 'Akun ini memiliki profil guru/siswa. Hapus melalui menu Guru atau Siswa agar profil ikut tertangani.');
        }

        if ($user->hasAttendanceActivity()) {
            return back()->with('error', 'Akun memiliki riwayat aktivitas absensi dan tidak boleh dihapus. Nonaktifkan akun sebagai gantinya.');
        }

        if ($user->isAdmin() && User::query()->role(User::ROLE_ADMIN)->where('is_active', true)->count() <= 1) {
            return back()->with('error', 'Ini satu-satunya admin aktif. Tambahkan admin lain sebelum menghapus.');
        }

        DB::transaction(function () use ($user) {
            AuditLogger::log('user.deleted', $user, ['name' => $user->name, 'role' => $user->role]);
            $user->delete();
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun {$user->name} berhasil dihapus.");
    }
}
