<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $request->user()->teacher;

        $schedules = $teacher
            ? Schedule::query()
                ->with(['schoolClass', 'subject'])
                ->withCount('attendanceSessions')
                ->forTeacher($teacher->id)
                ->orderByRaw("FIELD(day_of_week, 'monday','tuesday','wednesday','thursday','friday','saturday')")
                ->orderBy('start_time')
                ->get()
                ->groupBy('day_of_week')
            : collect();

        return view('guru.schedules.index', [
            'teacher' => $teacher,
            'schedules' => $schedules,
        ]);
    }
}
