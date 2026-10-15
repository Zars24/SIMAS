<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Student;
use App\Services\AttendanceStats;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly AttendanceStats $stats)
    {
    }

    public function index(Request $request): View
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return view('guru.dashboard', ['teacher' => null]);
        }

        $todayKey = strtolower(now()->format('l'));
        $today = now()->toDateString();

        $todaySchedules = Schedule::query()
            ->with(['schoolClass', 'subject'])
            ->withExists(['attendanceSessions as filled_today' => fn ($query) => $query->whereDate('session_date', $today)])
            ->forTeacher($teacher->id)
            ->active()
            ->where('day_of_week', $todayKey)
            ->orderBy('start_time')
            ->get();

        $classIds = Schedule::query()
            ->forTeacher($teacher->id)
            ->active()
            ->distinct()
            ->pluck('class_id');

        $studentCount = Student::query()
            ->whereIn('class_id', $classIds)
            ->whereHas('user', fn ($query) => $query->active())
            ->count();

        $monthCounts = $this->stats->statusCounts($teacher->id, now()->startOfMonth(), now()->endOfDay());

        $weeklySchedules = Schedule::query()
            ->with(['schoolClass', 'subject'])
            ->forTeacher($teacher->id)
            ->orderByDay()
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        return view('guru.dashboard', [
            'teacher' => $teacher,
            'todaySchedules' => $todaySchedules,
            'studentCount' => $studentCount,
            'classCount' => $classIds->count(),
            'monthCounts' => $monthCounts,
            'monthPercentage' => $this->stats->percentage($monthCounts),
            'weeklySchedules' => $weeklySchedules,
            'trend' => $this->stats->trendSeries(now()->subDays(13)->startOfDay(), now()->endOfDay(), $teacher->id),
        ]);
    }
}
