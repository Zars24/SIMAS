<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user()->student;

        if (! $student) {
            return view('siswa.attendance', [
                'student' => null,
                'attendances' => null,
                'summary' => null,
                'percentage' => null,
                'subjects' => collect(),
                'filters' => [],
            ]);
        }

        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'status' => ['nullable', 'in:'.implode(',', array_keys(Attendance::STATUSES))],
            'subject_id' => ['nullable', 'integer'],
        ]);

        $filters = [
            'month' => $validated['month'] ?? null,
            'status' => $validated['status'] ?? null,
            'subject_id' => $validated['subject_id'] ?? null,
        ];

        $monthStart = $filters['month']
            ? Carbon::createFromFormat('Y-m', $filters['month'])->startOfMonth()
            : null;
        $monthEnd = $monthStart?->copy()->endOfMonth();

        // Semua query dibatasi ke siswa yang sedang login (bukan dari parameter URL).
        $attendances = Attendance::query()
            ->forStudent($student->id)
            ->with(['session.schedule.subject', 'session.schedule.teacher.user'])
            ->when($monthStart, fn (Builder $query) => $query->whereHas(
                'session',
                fn (Builder $sessionQuery) => $sessionQuery->whereBetween('session_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ))
            ->withStatus($filters['status'])
            ->when($filters['subject_id'], fn (Builder $query) => $query->whereHas(
                'session.schedule',
                fn (Builder $scheduleQuery) => $scheduleQuery->where('subject_id', $filters['subject_id'])
            ))
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
            ->orderByDesc('attendance_sessions.session_date')
            ->orderByDesc('attendances.id')
            ->select('attendances.*')
            ->paginate(15)
            ->withQueryString();

        // Ringkasan mengikuti filter periode (konsisten dengan tabel).
        $summary = $student->attendanceSummary($monthStart, $monthEnd);

        $subjects = Subject::query()
            ->whereHas('schedules', fn (Builder $query) => $query->where('class_id', $student->class_id))
            ->orderBy('name')
            ->get();

        return view('siswa.attendance', [
            'student' => $student,
            'attendances' => $attendances,
            'summary' => $summary,
            'percentage' => array_sum($summary) > 0
                ? round($summary[Attendance::STATUS_HADIR] / array_sum($summary) * 100, 1)
                : null,
            'subjects' => $subjects,
            'filters' => $filters,
        ]);
    }
}
