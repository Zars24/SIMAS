<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Http\Requests\Admin\UpdateTeacherRequest;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $teachers = Teacher::query()
            ->with('user')
            ->withCount(['schedules', 'homeroomClasses'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q')->toString().'%';

                $query->where(function ($inner) use ($term) {
                    $inner->where('teacher_number', 'like', $term)
                        ->orWhereHas('user', function ($userQuery) use ($term) {
                            $userQuery->where('name', 'like', $term)
                                ->orWhere('username', 'like', $term)
                                ->orWhere('email', 'like', $term);
                        });
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->whereHas(
                'user',
                fn ($userQuery) => $userQuery->where('is_active', $request->string('status')->toString() === 'active')
            ))
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->orderBy('users.name')
            ->select('teachers.*')
            ->paginate(10)
            ->withQueryString();

        return view('admin.teachers.index', [
            'teachers' => $teachers,
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('admin.teachers.create', [
            'teacher' => new Teacher(),
            'user' => new User(['is_active' => true, 'role' => User::ROLE_GURU]),
        ]);
    }

    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $teacher = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => User::ROLE_GURU,
                'is_active' => $data['is_active'],
            ]);

            return $user->teacher()->create([
                'teacher_number' => $data['teacher_number'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);
        });

        AuditLogger::log('teacher.created', $teacher, ['name' => $teacher->user->name]);

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', "Guru {$teacher->user->name} berhasil ditambahkan.");
    }

    public function edit(Teacher $teacher): View
    {
        $teacher->load('user');

        return view('admin.teachers.edit', [
            'teacher' => $teacher,
            'user' => $teacher->user,
        ]);
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($teacher, $data) {
            $userData = [
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'is_active' => $data['is_active'],
            ];

            if (! empty($data['password'])) {
                $userData['password'] = $data['password'];
            }

            $teacher->user->update($userData);

            $teacher->update([
                'teacher_number' => $data['teacher_number'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);
        });

        AuditLogger::log('teacher.updated', $teacher, ['name' => $teacher->user->name]);

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        $teacher->load('user');

        if ($teacher->schedules()->exists() || $teacher->user->hasAttendanceActivity()) {
            return back()->with('error', 'Guru ini memiliki riwayat jadwal/absensi dan tidak boleh dihapus. Nonaktifkan akunnya sebagai gantinya.');
        }

        DB::transaction(function () use ($teacher) {
            AuditLogger::log('teacher.deleted', $teacher, ['name' => $teacher->user->name]);
            $user = $teacher->user;

            $teacher->delete();
            $user->delete();
        });

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', 'Data guru berhasil dihapus.');
    }
}
