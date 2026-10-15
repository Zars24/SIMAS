<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\StoreAttendanceRequest;
use App\Http\Requests\Guru\UpdateAttendanceRequest;
use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Models\Student;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /** Jendela waktu mundur untuk membuat sesi baru (hari). */
    private const MAX_BACKDATE_DAYS = 90;

    public function index(Request $request): View
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return view('guru.attendance.index', [
                'teacher' => null,
                'schedules' => collect(),
                'recentSessions' => null,
                'date' => now(),
            ]);
        }

        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
        ]);

        $date = isset($validated['date'])
            ? Carbon::createFromFormat('Y-m-d', $validated['date'])->startOfDay()
            : now()->startOfDay();

        $dayKey = strtolower($date->format('l'));

        $schedules = Schedule::query()
            ->with(['schoolClass', 'subject'])
            ->withExists(['attendanceSessions as filled_today' => fn ($query) => $query->whereDate('session_date', $date->toDateString())])
            ->forTeacher($teacher->id)
            ->active()
            ->where('day_of_week', $dayKey)
            ->orderBy('start_time')
            ->get();

        $recentSessions = AttendanceSession::query()
            ->whereHas('schedule', fn ($query) => $query->where('teacher_id', $teacher->id))
            ->with(['schedule.schoolClass', 'schedule.subject'])
            ->withCount([
                'attendances as count_hadir' => fn ($query) => $query->where('status', 'hadir'),
                'attendances as count_izin' => fn ($query) => $query->where('status', 'izin'),
                'attendances as count_sakit' => fn ($query) => $query->where('status', 'sakit'),
                'attendances as count_alpa' => fn ($query) => $query->where('status', 'alpa'),
            ])
            ->orderByDesc('session_date')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'sesi')
            ->withQueryString();

        return view('guru.attendance.index', [
            'teacher' => $teacher,
            'schedules' => $schedules,
            'recentSessions' => $recentSessions,
            'date' => $date,
        ]);
    }

    public function fill(Schedule $schedule, Request $request): View|RedirectResponse
    {
        $this->authorize('fill', $schedule);

        $validated = $request->validate([
            'date' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
                'after_or_equal:'.now()->subDays(self::MAX_BACKDATE_DAYS)->toDateString(),
            ],
        ]);

        $date = Carbon::createFromFormat('Y-m-d', $validated['date'])->startOfDay();

        if (strtolower($date->format('l')) !== $schedule->day_of_week) {
            return redirect()
                ->route('guru.attendance.index', ['date' => $date->toDateString()])
                ->with('error', "Tanggal {$date->translatedFormat('d F Y')} bukan hari {$schedule->dayLabel()} — sesi tidak dapat diisi untuk tanggal tersebut.");
        }

        $schedule->load(['schoolClass', 'subject']);

        $session = $schedule->attendanceSessions()
            ->whereDate('session_date', $date)
            ->first();

        if ($session && ! $request->user()->can('update', $session)) {
            return redirect()
                ->route('guru.attendance.show', $session)
                ->with('error', 'Sesi ini berada di luar jendela koreksi sehingga hanya dapat dilihat.');
        }

        $students = $this->classStudents($schedule->class_id);

        $existing = $session
            ? $session->attendances()->get()->keyBy('student_id')
            : collect();

        return view('guru.attendance.form', [
            'schedule' => $schedule,
            'date' => $date,
            'session' => $session,
            'students' => $students,
            'existing' => $existing,
        ]);
    }

    public function store(StoreAttendanceRequest $request, Schedule $schedule): RedirectResponse
    {
        $data = $request->validated();
        $date = Carbon::createFromFormat('Y-m-d', $data['session_date'])->startOfDay();

        $existing = $schedule->attendanceSessions()
            ->whereDate('session_date', $date)
            ->first();

        if ($existing) {
            return redirect()
                ->route('guru.attendance.show', $existing)
                ->with('error', 'Sesi untuk tanggal tersebut sudah pernah dibuat. Silakan gunakan fitur koreksi bila perlu perubahan.');
        }

        $session = DB::transaction(function () use ($schedule, $data, $date, $request) {
            $session = $schedule->attendanceSessions()->create([
                'session_date' => $date->toDateString(),
                'topic' => $data['topic'] ?? null,
                'created_by' => $request->user()->id,
                'submitted_at' => now(),
            ]);

            $timestamp = now();

            $rows = collect($data['attendances'])
                ->map(fn (array $item, $studentId) => [
                    'student_id' => (int) $studentId,
                    'status' => $item['status'],
                    'note' => $item['note'] ?? null,
                    'recorded_by' => $request->user()->id,
                    'recorded_at' => $timestamp,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ])
                ->values()
                ->all();

            $session->attendances()->insert($rows);

            return $session;
        });

        AuditLogger::log('attendance.session_created', $session, [
            'schedule_id' => $session->schedule_id,
            'session_date' => $session->session_date->toDateString(),
            'counts' => $session->countsByStatus(),
        ]);

        return redirect()
            ->route('guru.attendance.show', $session)
            ->with('success', 'Absensi berhasil disimpan.');
    }

    public function show(AttendanceSession $session): View
    {
        $this->authorize('view', $session);

        $session->load([
            'schedule.schoolClass',
            'schedule.subject',
            'schedule.teacher.user',
            'attendances.student.user',
            'attendances.recorder',
            'creator',
        ]);

        return view('guru.attendance.show', [
            'session' => $session,
            'counts' => $session->countsByStatus(),
            'canEdit' => auth()->user()->can('update', $session),
        ]);
    }

    public function update(UpdateAttendanceRequest $request, AttendanceSession $session): RedirectResponse
    {
        $data = $request->validated();

        $changes = DB::transaction(function () use ($session, $data, $request) {
            $session->update([
                'topic' => $data['topic'] ?? null,
                'submitted_at' => now(),
            ]);

            $existing = $session->attendances()->get()->keyBy('student_id');
            $changes = [];
            $timestamp = now();

            foreach ($data['attendances'] as $studentId => $item) {
                $studentId = (int) $studentId;
                $current = $existing->get($studentId);

                if ($current) {
                    if ($current->status !== $item['status'] || ($current->note ?? null) !== ($item['note'] ?? null)) {
                        $changes[] = [
                            'student_id' => $studentId,
                            'from' => $current->status,
                            'to' => $item['status'],
                        ];

                        $current->update([
                            'status' => $item['status'],
                            'note' => $item['note'] ?? null,
                            'recorded_by' => $request->user()->id,
                            'recorded_at' => $timestamp,
                        ]);
                    }

                    continue;
                }

                $session->attendances()->create([
                    'student_id' => $studentId,
                    'status' => $item['status'],
                    'note' => $item['note'] ?? null,
                    'recorded_by' => $request->user()->id,
                    'recorded_at' => $timestamp,
                ]);

                $changes[] = [
                    'student_id' => $studentId,
                    'from' => null,
                    'to' => $item['status'],
                ];
            }

            return $changes;
        });

        AuditLogger::log('attendance.session_corrected', $session, [
            'schedule_id' => $session->schedule_id,
            'session_date' => $session->session_date->toDateString(),
            'changed' => count($changes),
            'changes' => array_slice($changes, 0, 50),
        ]);

        return redirect()
            ->route('guru.attendance.show', $session)
            ->with('success', count($changes) > 0
                ? 'Koreksi absensi tersimpan ('.count($changes).' perubahan).'
                : 'Tidak ada perubahan pada absensi.');
    }

    /**
     * Siswa aktif pada sebuah kelas, diurutkan berdasarkan nama.
     */
    private function classStudents(int $classId)
    {
        return Student::query()
            ->with('user')
            ->where('class_id', $classId)
            ->whereHas('user', fn ($query) => $query->active())
            ->join('users', 'users.id', '=', 'students.user_id')
            ->orderBy('users.name')
            ->select('students.*')
            ->get();
    }
}
