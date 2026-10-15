<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user()->student;

        $schedules = $student?->class_id
            ? Schedule::query()
                ->with(['subject', 'teacher.user'])
                ->forClass($student->class_id)
                ->active()
                ->orderByDay()
                ->orderBy('start_time')
                ->get()
                ->groupBy('day_of_week')
            : collect();

        return view('siswa.schedule', [
            'student' => $student,
            'schedules' => $schedules,
        ]);
    }
}
