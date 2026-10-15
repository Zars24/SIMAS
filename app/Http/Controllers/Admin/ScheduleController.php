<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreScheduleRequest;
use App\Http\Requests\Admin\UpdateScheduleRequest;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $schedules = Schedule::query()
            ->with(['schoolClass', 'teacher.user', 'subject'])
            ->when($request->filled('class_id'), fn ($query) => $query->where('class_id', $request->integer('class_id')))
            ->when($request->filled('teacher_id'), fn ($query) => $query->where('teacher_id', $request->integer('teacher_id')))
            ->when($request->filled('day'), fn ($query) => $query->where('day_of_week', $request->string('day')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status')->toString() === 'active'))
            ->orderByRaw("FIELD(day_of_week, 'monday','tuesday','wednesday','thursday','friday','saturday')")
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        return view('admin.schedules.index', [
            'schedules' => $schedules,
            'options' => $this->formOptions(),
            'filters' => $request->only(['class_id', 'teacher_id', 'day', 'status']),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.schedules.create', [
            'schedule' => new Schedule([
                'day_of_week' => 'monday',
                'is_active' => true,
                'class_id' => $request->integer('class_id') ?: null,
            ]),
            'options' => $this->formOptions(),
        ]);
    }

    public function store(StoreScheduleRequest $request): RedirectResponse
    {
        $schedule = Schedule::create($request->validated());

        AuditLogger::log('schedule.created', $schedule, [
            'class' => $schedule->schoolClass?->name,
            'subject' => $schedule->subject?->name,
            'day' => $schedule->dayLabel(),
        ]);

        return redirect()
            ->route('admin.schedules.index')
            ->with('success', 'Jadwal berhasil dibuat.');
    }

    public function edit(Schedule $schedule): View
    {
        return view('admin.schedules.edit', [
            'schedule' => $schedule->load(['schoolClass', 'teacher.user', 'subject']),
            'options' => $this->formOptions(),
        ]);
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule): RedirectResponse
    {
        $schedule->update($request->validated());

        AuditLogger::log('schedule.updated', $schedule, [
            'class' => $schedule->schoolClass?->name,
            'subject' => $schedule->subject?->name,
            'day' => $schedule->dayLabel(),
        ]);

        return redirect()
            ->route('admin.schedules.index')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        if ($schedule->attendanceSessions()->exists()) {
            return back()->with('error', 'Jadwal ini sudah memiliki riwayat absensi dan tidak boleh dihapus. Nonaktifkan jadwal sebagai gantinya.');
        }

        AuditLogger::log('schedule.deleted', $schedule, [
            'class' => $schedule->schoolClass?->name,
            'subject' => $schedule->subject?->name,
        ]);

        $schedule->delete();

        return redirect()
            ->route('admin.schedules.index')
            ->with('success', 'Jadwal berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'classes' => SchoolClass::query()->with([])->orderBy('name')->get(),
            'teachers' => Teacher::query()
                ->with('user')
                ->join('users', 'users.id', '=', 'teachers.user_id')
                ->orderBy('users.name')
                ->select('teachers.*')
                ->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'days' => Schedule::DAYS,
        ];
    }
}
