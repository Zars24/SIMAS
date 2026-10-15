<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceRecapController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        $attendances = $this->baseQuery($filters)
            ->join('attendance_sessions as recap_sessions', 'recap_sessions.id', '=', 'attendances.attendance_session_id')
            ->join('students as recap_students', 'recap_students.id', '=', 'attendances.student_id')
            ->join('users as recap_users', 'recap_users.id', '=', 'recap_students.user_id')
            ->orderByDesc('recap_sessions.session_date')
            ->orderBy('recap_users.name')
            ->select('attendances.*')
            ->paginate(20)
            ->withQueryString();

        return view('admin.attendance.index', [
            'attendances' => $attendances,
            'classSummaries' => $this->classSummaries($filters),
            'totals' => $this->totals($filters),
            'options' => $this->formOptions(),
            'filters' => $filters,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);

        $filename = sprintf('rekap-absensi_%s_%s.csv', $filters['date_from'], $filters['date_to']);

        return response()->streamDownload(function () use ($filters) {
            $handle = fopen('php://output', 'w');

            // BOM agar Excel membaca UTF-8 dengan benar.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Tanggal', 'Kelas', 'Mata Pelajaran', 'Guru', 'NIS', 'Nama Siswa',
                'Status', 'Catatan', 'Dicatat Oleh', 'Waktu Dicatat',
            ]);

            $this->baseQuery($filters)
                ->with(['student.user', 'session.schedule.schoolClass', 'session.schedule.subject', 'session.schedule.teacher.user', 'recorder'])
                ->join('attendance_sessions as exp_sessions', 'exp_sessions.id', '=', 'attendances.attendance_session_id')
                ->join('students as exp_students', 'exp_students.id', '=', 'attendances.student_id')
                ->join('users as exp_users', 'exp_users.id', '=', 'exp_students.user_id')
                ->orderByDesc('exp_sessions.session_date')
                ->orderBy('exp_users.name')
                ->select('attendances.*')
                ->chunk(300, function (Collection $rows) use ($handle) {
                    foreach ($rows as $attendance) {
                        fputcsv($handle, [
                            $attendance->session->session_date->format('Y-m-d'),
                            $attendance->session->schedule->schoolClass->name ?? '-',
                            $attendance->session->schedule->subject->name ?? '-',
                            $attendance->session->schedule->teacher->user->name ?? '-',
                            $attendance->student->student_number,
                            $attendance->student->user->name,
                            $attendance->statusLabel(),
                            $attendance->note ?? '',
                            $attendance->recorder->name ?? '-',
                            $attendance->recorded_at?->format('Y-m-d H:i'),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function show(AttendanceSession $session): View
    {
        $session->load([
            'schedule.schoolClass',
            'schedule.subject',
            'schedule.teacher.user',
            'attendances.student.user',
            'attendances.recorder',
            'creator',
        ]);

        return view('admin.attendance.show', [
            'session' => $session,
            'counts' => $session->countsByStatus(),
        ]);
    }

    /**
     * @return array<string, string|null>
     */
    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'class_id' => ['nullable', 'integer'],
            'subject_id' => ['nullable', 'integer'],
            'teacher_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:'.implode(',', array_keys(Attendance::STATUSES))],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        return [
            'date_from' => $validated['date_from'] ?? now()->startOfMonth()->toDateString(),
            'date_to' => $validated['date_to'] ?? now()->toDateString(),
            'class_id' => $validated['class_id'] ?? null,
            'subject_id' => $validated['subject_id'] ?? null,
            'teacher_id' => $validated['teacher_id'] ?? null,
            'status' => $validated['status'] ?? null,
            'q' => $validated['q'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function baseQuery(array $filters): Builder
    {
        return Attendance::query()
            ->with([
                'student.user',
                'student.schoolClass',
                'session.schedule.schoolClass',
                'session.schedule.subject',
                'session.schedule.teacher.user',
                'recorder',
            ])
            ->whereHas('session', function (Builder $query) use ($filters) {
                $query->whereBetween('session_date', [$filters['date_from'], $filters['date_to']]);

                if ($filters['class_id'] || $filters['subject_id'] || $filters['teacher_id']) {
                    $query->whereHas('schedule', function (Builder $scheduleQuery) use ($filters) {
                        $scheduleQuery
                            ->when($filters['class_id'], fn ($q) => $q->where('class_id', $filters['class_id']))
                            ->when($filters['subject_id'], fn ($q) => $q->where('subject_id', $filters['subject_id']))
                            ->when($filters['teacher_id'], fn ($q) => $q->where('teacher_id', $filters['teacher_id']));
                    });
                }
            })
            ->when($filters['status'], fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['q'], fn ($query) => $query->whereHas(
                'student.user',
                fn ($userQuery) => $userQuery
                    ->where('name', 'like', '%'.$filters['q'].'%')
                    ->orWhere('username', 'like', '%'.$filters['q'].'%')
            ));
    }

    /**
     * Ringkasan per kelas untuk periode/filter yang sama.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function classSummaries(array $filters): Collection
    {
        $rows = Attendance::query()
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
            ->join('schedules', 'schedules.id', '=', 'attendance_sessions.schedule_id')
            ->join('classes', 'classes.id', '=', 'schedules.class_id')
            ->whereBetween('attendance_sessions.session_date', [$filters['date_from'], $filters['date_to']])
            ->when($filters['class_id'], fn ($query) => $query->where('schedules.class_id', $filters['class_id']))
            ->when($filters['subject_id'], fn ($query) => $query->where('schedules.subject_id', $filters['subject_id']))
            ->when($filters['teacher_id'], fn ($query) => $query->where('schedules.teacher_id', $filters['teacher_id']))
            ->when($filters['status'], fn ($query) => $query->where('attendances.status', $filters['status']))
            ->when($filters['q'], fn ($query) => $query->whereHas('student.user', function ($userQuery) use ($filters) {
                $userQuery->where('name', 'like', '%'.$filters['q'].'%');
            }))
            ->groupBy('classes.id', 'classes.name', 'classes.academic_year')
            ->selectRaw('classes.id as class_id, classes.name as class_name, classes.academic_year as academic_year')
            ->selectRaw("SUM(attendances.status = 'hadir') as hadir")
            ->selectRaw("SUM(attendances.status = 'izin') as izin")
            ->selectRaw("SUM(attendances.status = 'sakit') as sakit")
            ->selectRaw("SUM(attendances.status = 'alpa') as alpa")
            ->selectRaw('COUNT(*) as total')
            ->orderBy('classes.name')
            ->get();

        return $rows->map(function ($row) {
            $total = (int) $row->total;

            return [
                'class_id' => $row->class_id,
                'class_name' => $row->class_name,
                'academic_year' => $row->academic_year,
                'hadir' => (int) $row->hadir,
                'izin' => (int) $row->izin,
                'sakit' => (int) $row->sakit,
                'alpa' => (int) $row->alpa,
                'total' => $total,
                'percentage' => $total > 0 ? round((int) $row->hadir / $total * 100, 1) : null,
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, int|float|null>
     */
    private function totals(array $filters): array
    {
        $counts = Attendance::query()
            ->whereHas('session', function (Builder $query) use ($filters) {
                $query->whereBetween('session_date', [$filters['date_from'], $filters['date_to']]);

                if ($filters['class_id'] || $filters['subject_id'] || $filters['teacher_id']) {
                    $query->whereHas('schedule', function (Builder $scheduleQuery) use ($filters) {
                        $scheduleQuery
                            ->when($filters['class_id'], fn ($q) => $q->where('class_id', $filters['class_id']))
                            ->when($filters['subject_id'], fn ($q) => $q->where('subject_id', $filters['subject_id']))
                            ->when($filters['teacher_id'], fn ($q) => $q->where('teacher_id', $filters['teacher_id']));
                    });
                }
            })
            ->when($filters['status'], fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['q'], fn ($query) => $query->whereHas(
                'student.user',
                fn ($userQuery) => $userQuery->where('name', 'like', '%'.$filters['q'].'%')
            ))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $summary = [];

        foreach (array_keys(Attendance::STATUSES) as $status) {
            $summary[$status] = (int) ($counts[$status] ?? 0);
        }

        $total = array_sum($summary);
        $summary['total'] = $total;
        $summary['percentage'] = $total > 0 ? round($summary['hadir'] / $total * 100, 1) : null;

        return $summary;
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'classes' => SchoolClass::query()->orderBy('name')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'teachers' => Teacher::query()
                ->with('user')
                ->join('users', 'users.id', '=', 'teachers.user_id')
                ->orderBy('users.name')
                ->select('teachers.*')
                ->get(),
        ];
    }
}
