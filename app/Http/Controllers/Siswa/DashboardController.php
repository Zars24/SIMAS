<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user()->student;

        if (! $student) {
            return view('siswa.dashboard', ['student' => null]);
        }

        $student->load('schoolClass.homeroomTeacher.user');

        $summary = $student->attendanceSummary();
        $percentage = $student->attendancePercentage();

        // Jadwal terdekat: hari ini atau hari sekolah berikutnya (maks. 7 hari ke depan).
        $upcoming = null;

        for ($i = 0; $i <= 7; $i++) {
            $day = now()->addDays($i);
            $dayKey = strtolower($day->format('l'));

            if ($dayKey === 'sunday') {
                continue;
            }

            $schedules = $student->class_id
                ? Schedule::query()
                    ->with(['subject', 'teacher.user'])
                    ->forClass($student->class_id)
                    ->active()
                    ->where('day_of_week', $dayKey)
                    ->orderBy('start_time')
                    ->get()
                : collect();

            if ($schedules->isNotEmpty()) {
                $upcoming = ['date' => $day, 'schedules' => $schedules];
                break;
            }
        }

        $recentAttendances = $student->attendances()
            ->with(['session.schedule.schoolClass', 'session.schedule.subject', 'session.schedule.teacher.user'])
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
            ->orderByDesc('attendance_sessions.session_date')
            ->orderByDesc('attendances.id')
            ->select('attendances.*')
            ->limit(5)
            ->get();

        $monthCounts = $student->attendanceSummary(now()->startOfMonth(), now()->endOfDay());

        return view('siswa.dashboard', [
            'student' => $student,
            'summary' => $summary,
            'percentage' => $percentage,
            'upcoming' => $upcoming,
            'recentAttendances' => $recentAttendances,
            'monthCounts' => $monthCounts,
            'monthPercentage' => $student->attendancePercentage(now()->startOfMonth(), now()->endOfDay()),
            'today' => Carbon::today(),
        ]);
    }
}
