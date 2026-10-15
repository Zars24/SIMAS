<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClassRequest;
use App\Http\Requests\Admin\UpdateClassRequest;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    public function index(Request $request): View
    {
        $classes = SchoolClass::query()
            ->with('homeroomTeacher.user')
            ->withCount(['students', 'schedules'])
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q')->toString().'%'))
            ->when($request->filled('academic_year'), fn ($query) => $query->where('academic_year', $request->string('academic_year')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status')->toString() === 'active'))
            ->orderByDesc('academic_year')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.classes.index', [
            'classes' => $classes,
            'academicYears' => SchoolClass::query()->distinct()->orderByDesc('academic_year')->pluck('academic_year'),
            'filters' => $request->only(['q', 'academic_year', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('admin.classes.create', [
            'schoolClass' => new SchoolClass(['is_active' => true]),
            'teachers' => $this->teacherOptions(),
        ]);
    }

    public function store(StoreClassRequest $request): RedirectResponse
    {
        $schoolClass = SchoolClass::create($request->validated());

        AuditLogger::log('class.created', $schoolClass, ['name' => $schoolClass->displayName()]);

        return redirect()
            ->route('admin.classes.index')
            ->with('success', "Kelas {$schoolClass->displayName()} berhasil dibuat.");
    }

    public function edit(SchoolClass $schoolClass): View
    {
        return view('admin.classes.edit', [
            'schoolClass' => $schoolClass,
            'teachers' => $this->teacherOptions(),
        ]);
    }

    public function update(UpdateClassRequest $request, SchoolClass $schoolClass): RedirectResponse
    {
        $schoolClass->update($request->validated());

        AuditLogger::log('class.updated', $schoolClass, ['name' => $schoolClass->displayName()]);

        return redirect()
            ->route('admin.classes.index')
            ->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $schoolClass): RedirectResponse
    {
        if ($schoolClass->students()->exists()) {
            return back()->with('error', 'Kelas masih memiliki siswa. Pindahkan siswa terlebih dahulu.');
        }

        if ($schoolClass->schedules()->exists()) {
            return back()->with('error', 'Kelas masih memiliki jadwal. Hapus jadwalnya terlebih dahulu agar riwayat absensi tidak rusak.');
        }

        AuditLogger::log('class.deleted', $schoolClass, ['name' => $schoolClass->displayName()]);

        $schoolClass->delete();

        return redirect()
            ->route('admin.classes.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }

    private function teacherOptions()
    {
        return Teacher::query()
            ->with('user')
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->orderBy('users.name')
            ->select('teachers.*')
            ->get();
    }
}
