<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\AuditLog;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\AttendanceStats;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly AttendanceStats $stats)
    {
    }

    public function index(Request $request): View
    {
        $period = in_array($request->integer('period'), [7, 30, 90], true)
            ? $request->integer('period')
            : 30;

        $from = now()->subDays($period - 1)->startOfDay();
        $to = now()->endOfDay();

        $todayKey = strtolower(now()->format('l'));
        $today = now()->toDateString();

        $stats = [
            'students' => Student::query()->whereHas('user', fn ($query) => $query->active())->count(),
            'teachers' => Teacher::query()->whereHas('user', fn ($query) => $query->active())->count(),
            'classes' => SchoolClass::query()->active()->count(),
            'sessions_today' => AttendanceSession::query()->whereDate('session_date', $today)->count(),
        ];

        $todayCounts = $this->stats->statusCounts(null, now()->startOfDay(), now()->endOfDay());

        $todaySchedules = Schedule::query()
            ->with(['schoolClass', 'subject', 'teacher.user'])
            ->withExists(['attendanceSessions as filled_today' => fn ($query) => $query->whereDate('session_date', $today)])
            ->where('day_of_week', $todayKey)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        $periodCounts = $this->stats->statusCounts(null, $from, $to);
        $trend = $this->stats->trendSeries($from, $to);

        $recentLogs = AuditLog::query()
            ->with('user')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        return view('admin.dashboard', [
            'period' => $period,
            'stats' => $stats,
            'todayCounts' => $todayCounts,
            'todaySchedules' => $todaySchedules,
            'periodCounts' => $periodCounts,
            'periodPercentage' => $this->stats->percentage($periodCounts),
            'trend' => $trend,
            'recentLogs' => $recentLogs,
            'today' => Carbon::parse($today),
        ]);
    }
}
