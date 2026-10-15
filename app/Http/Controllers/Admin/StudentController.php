<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $students = Student::query()
            ->with(['user', 'schoolClass'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q')->toString().'%';

                $query->where(function ($inner) use ($term) {
                    $inner->where('student_number', 'like', $term)
                        ->orWhereHas('user', function ($userQuery) use ($term) {
                            $userQuery->where('name', 'like', $term)
                                ->orWhere('username', 'like', $term)
                                ->orWhere('email', 'like', $term);
                        });
                });
            })
            ->when($request->filled('class_id'), fn ($query) => $query->where('class_id', $request->integer('class_id')))
            ->when($request->filled('status'), fn ($query) => $query->whereHas(
                'user',
                fn ($userQuery) => $userQuery->where('is_active', $request->string('status')->toString() === 'active')
            ))
            ->join('users', 'users.id', '=', 'students.user_id')
            ->orderBy('users.name')
            ->select('students.*')
            ->paginate(10)
            ->withQueryString();

        return view('admin.students.index', [
            'students' => $students,
            'classes' => SchoolClass::query()->orderBy('name')->get(),
            'filters' => $request->only(['q', 'class_id', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('admin.students.create', [
            'student' => new Student(),
            'user' => new User(['is_active' => true, 'role' => User::ROLE_SISWA]),
            'classes' => SchoolClass::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $student = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => User::ROLE_SISWA,
                'is_active' => $data['is_active'],
            ]);

            return $user->student()->create([
                'class_id' => $data['class_id'] ?? null,
                'student_number' => $data['student_number'],
                'birth_date' => $data['birth_date'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);
        });

        AuditLogger::log('student.created', $student, ['name' => $student->user->name]);

        return redirect()
            ->route('admin.students.index')
            ->with('success', "Siswa {$student->user->name} berhasil ditambahkan.");
    }

    public function show(Student $student): View
    {
        $student->load(['user', 'schoolClass.homeroomTeacher.user']);

        return view('admin.students.show', [
            'student' => $student,
            'summary' => $student->attendanceSummary(),
            'percentage' => $student->attendancePercentage(),
            'attendances' => $student->attendances()
                ->with(['session.schedule.subject', 'session.schedule.teacher.user', 'recorder'])
                ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
                ->orderByDesc('attendance_sessions.session_date')
                ->orderByDesc('attendances.id')
                ->select('attendances.*')
                ->paginate(10, ['*'], 'riwayat'),
        ]);
    }

    public function edit(Student $student): View
    {
        $student->load('user');

        return view('admin.students.edit', [
            'student' => $student,
            'user' => $student->user,
            'classes' => SchoolClass::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($student, $data) {
            $userData = [
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'is_active' => $data['is_active'],
            ];

            if (! empty($data['password'])) {
                $userData['password'] = $data['password'];
            }

            $student->user->update($userData);

            $student->update([
                'class_id' => $data['class_id'] ?? null,
                'student_number' => $data['student_number'],
                'birth_date' => $data['birth_date'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);
        });

        AuditLogger::log('student.updated', $student, [
            'name' => $student->user->name,
            'class_id' => $student->class_id,
        ]);

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->load('user');

        if ($student->attendances()->exists()) {
            return back()->with('error', 'Siswa ini memiliki riwayat absensi dan tidak boleh dihapus. Nonaktifkan akunnya sebagai gantinya agar riwayat tetap utuh.');
        }

        DB::transaction(function () use ($student) {
            AuditLogger::log('student.deleted', $student, ['name' => $student->user->name]);
            $user = $student->user;

            $student->delete();
            $user->delete();
        });

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Data siswa berhasil dihapus.');
    }
}
