<?php

namespace Tests\Feature\Guru;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** Tanggal lampau (≤7 hari) yang jatuh pada hari sekolah. */
    private function pastSchoolDate(int $daysAgo = 1): Carbon
    {
        $date = now()->subDays($daysAgo)->startOfDay();

        while (! in_array(strtolower($date->format('l')), Schedule::DAY_ORDER, true)) {
            $date->subDay();
        }

        return $date;
    }

    /**
     * @return array{teacher: Teacher, user: User, schedule: Schedule, students: \Illuminate\Support\Collection<int, Student>, date: Carbon}
     */
    private function scenario(int $studentCount = 3, ?Carbon $date = null): array
    {
        $user = User::factory()->guru()->create();
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);

        $date ??= $this->pastSchoolDate();

        $class = SchoolClass::factory()->create();
        $subject = Subject::factory()->create();

        $schedule = Schedule::factory()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'day_of_week' => strtolower($date->format('l')),
        ]);

        $students = Student::factory()
            ->count($studentCount)
            ->inClass($class)
            ->create();

        return compact('teacher', 'user', 'schedule', 'students', 'date');
    }

    public function test_guru_dapat_mengisi_absensi_kelas_yang_ditugaskan(): void
    {
        ['user' => $guru, 'schedule' => $schedule, 'students' => $students, 'date' => $date] = $this->scenario();

        $payload = [
            'session_date' => $date->toDateString(),
            'topic' => 'Pengantar Aljabar',
            'attendances' => $students->mapWithKeys(fn (Student $student, int $index) => [
                $student->id => ['status' => $index === 0 ? 'izin' : 'hadir'],
            ])->all(),
        ];

        $this->actingAs($guru)
            ->post(route('guru.attendance.store', $schedule), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('attendance_sessions', [
            'schedule_id' => $schedule->id,
            'session_date' => $date->toDateString(),
            'topic' => 'Pengantar Aljabar',
        ]);

        $this->assertDatabaseCount('attendances', 3);
        $this->assertDatabaseHas('attendances', [
            'student_id' => $students->first()->id,
            'status' => 'izin',
        ]);

        $session = AttendanceSession::query()->firstOrFail();
        $this->assertNotNull($session->submitted_at);
        $this->assertSame($guru->id, $session->created_by);
    }

    public function test_guru_tidak_dapat_mengisi_absensi_kelas_guru_lain(): void
    {
        ['schedule' => $schedule, 'students' => $students, 'date' => $date] = $this->scenario();

        $lain = User::factory()->guru()->create();
        Teacher::factory()->create(['user_id' => $lain->id]);

        $this->actingAs($lain)
            ->get(route('guru.attendance.fill', ['schedule' => $schedule, 'date' => $date->toDateString()]))
            ->assertForbidden();

        $this->actingAs($lain)
            ->post(route('guru.attendance.store', $schedule), [
                'session_date' => $date->toDateString(),
                'attendances' => $students->mapWithKeys(fn (Student $s) => [$s->id => ['status' => 'hadir']])->all(),
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('attendance_sessions', 0);
    }

    public function test_semua_siswa_wajib_memiliki_status(): void
    {
        ['user' => $guru, 'schedule' => $schedule, 'students' => $students, 'date' => $date] = $this->scenario();

        // Hanya mengirim sebagian siswa.
        $partial = $students->take(1)->mapWithKeys(fn (Student $s) => [$s->id => ['status' => 'hadir']])->all();

        $this->actingAs($guru)
            ->post(route('guru.attendance.store', $schedule), [
                'session_date' => $date->toDateString(),
                'attendances' => $partial,
            ])
            ->assertSessionHasErrors('attendances');

        $this->assertDatabaseCount('attendance_sessions', 0);
    }

    public function test_status_di_luar_daftar_ditolak(): void
    {
        ['user' => $guru, 'schedule' => $schedule, 'students' => $students, 'date' => $date] = $this->scenario();

        $payload = $students->mapWithKeys(fn (Student $s) => [$s->id => ['status' => 'bolos']])->all();

        $this->actingAs($guru)
            ->post(route('guru.attendance.store', $schedule), [
                'session_date' => $date->toDateString(),
                'attendances' => $payload,
            ])
            ->assertSessionHasErrors("attendances.{$students->first()->id}.status");
    }

    public function test_tanggal_sesi_harus_sesuai_hari_jadwal(): void
    {
        ['user' => $guru, 'schedule' => $schedule, 'students' => $students, 'date' => $date] = $this->scenario();

        // Geser satu hari agar hari berbeda dari jadwal.
        $wrongDate = $date->copy()->addDay();

        if (strtolower($wrongDate->format('l')) === $schedule->day_of_week) {
            $wrongDate->addDay();
        }

        $this->actingAs($guru)
            ->get(route('guru.attendance.fill', ['schedule' => $schedule, 'date' => $wrongDate->toDateString()]))
            ->assertRedirect(route('guru.attendance.index', ['date' => $wrongDate->toDateString()]))
            ->assertSessionHas('error');
    }

    public function test_sesi_duplikat_untuk_jadwal_dan_tanggal_yang_sama_ditolak(): void
    {
        ['user' => $guru, 'schedule' => $schedule, 'students' => $students, 'date' => $date] = $this->scenario();

        $payload = [
            'session_date' => $date->toDateString(),
            'attendances' => $students->mapWithKeys(fn (Student $s) => [$s->id => ['status' => 'hadir']])->all(),
        ];

        $this->actingAs($guru)->post(route('guru.attendance.store', $schedule), $payload);

        // Percobaan kedua untuk jadwal + tanggal yang sama.
        $this->actingAs($guru)
            ->post(route('guru.attendance.store', $schedule), $payload)
            ->assertSessionHas('error');

        $this->assertDatabaseCount('attendance_sessions', 1);
        $this->assertDatabaseCount('attendances', 3);
    }

    public function test_constraint_database_menolak_absensi_ganda_per_siswa_per_sesi(): void
    {
        ['schedule' => $schedule, 'students' => $students, 'date' => $date] = $this->scenario();

        $session = AttendanceSession::factory()->create([
            'schedule_id' => $schedule->id,
            'session_date' => $date->toDateString(),
        ]);

        Attendance::factory()->create([
            'attendance_session_id' => $session->id,
            'student_id' => $students->first()->id,
        ]);

        $this->expectException(QueryException::class);

        Attendance::factory()->create([
            'attendance_session_id' => $session->id,
            'student_id' => $students->first()->id,
        ]);
    }

    public function test_constraint_database_menolak_sesi_ganda_per_jadwal_per_tanggal(): void
    {
        ['schedule' => $schedule, 'date' => $date] = $this->scenario();

        AttendanceSession::factory()->create([
            'schedule_id' => $schedule->id,
            'session_date' => $date->toDateString(),
        ]);

        $this->expectException(QueryException::class);

        AttendanceSession::factory()->create([
            'schedule_id' => $schedule->id,
            'session_date' => $date->toDateString(),
        ]);
    }

    public function test_guru_dapat_mengoreksi_absensi_dalam_jendela_edit(): void
    {
        ['user' => $guru, 'schedule' => $schedule, 'students' => $students, 'date' => $date] = $this->scenario();

        $this->actingAs($guru)->post(route('guru.attendance.store', $schedule), [
            'session_date' => $date->toDateString(),
            'attendances' => $students->mapWithKeys(fn (Student $s) => [$s->id => ['status' => 'hadir']])->all(),
        ]);

        $session = AttendanceSession::query()->firstOrFail();
        $target = $students->first();

        $this->actingAs($guru)
            ->put(route('guru.attendance.update', $session), [
                'topic' => 'Koreksi materi',
                'attendances' => $students->mapWithKeys(fn (Student $s) => [
                    $s->id => ['status' => $s->is($target) ? 'alpa' : 'hadir'],
                ])->all(),
            ])
            ->assertRedirect(route('guru.attendance.show', $session));

        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $session->id,
            'student_id' => $target->id,
            'status' => 'alpa',
        ]);

        $this->assertDatabaseHas('attendance_sessions', [
            'id' => $session->id,
            'topic' => 'Koreksi materi',
        ]);

        $this->assertDatabaseHas('audit_logs', ['action' => 'attendance.session_corrected']);
    }

    public function test_koreksi_di_luar_jendela_edit_ditolak(): void
    {
        $oldDate = now()->subDays(30)->startOfDay();

        while (! in_array(strtolower($oldDate->format('l')), Schedule::DAY_ORDER, true)) {
            $oldDate->subDay();
        }

        ['user' => $guru, 'schedule' => $schedule, 'students' => $students] = $this->scenario(2, $oldDate);

        $session = AttendanceSession::factory()->create([
            'schedule_id' => $schedule->id,
            'session_date' => $oldDate->toDateString(),
        ]);

        foreach ($students as $student) {
            Attendance::factory()->create([
                'attendance_session_id' => $session->id,
                'student_id' => $student->id,
            ]);
        }

        $this->assertFalse($session->fresh()->isWithinEditWindow());

        $this->actingAs($guru)
            ->put(route('guru.attendance.update', $session), [
                'attendances' => $students->mapWithKeys(fn (Student $s) => [$s->id => ['status' => 'alpa']])->all(),
            ])
            ->assertForbidden();
    }

    public function test_guru_hanya_melihat_jadwal_yang_ditugaskan(): void
    {
        ['user' => $guru, 'schedule' => $milik] = $this->scenario();

        $lain = User::factory()->guru()->create();
        $teacherLain = Teacher::factory()->create(['user_id' => $lain->id]);
        $scheduleLain = Schedule::factory()->create([
            'teacher_id' => $teacherLain->id,
            'subject_id' => Subject::factory()->create()->id,
        ]);

        $this->actingAs($guru)
            ->get(route('guru.schedules.index'))
            ->assertOk()
            ->assertSee($milik->subject->name)
            ->assertDontSee($scheduleLain->subject->name);

        $this->actingAs($guru)
            ->get(route('guru.attendance.show', AttendanceSession::factory()->create([
                'schedule_id' => $scheduleLain->id,
                'session_date' => now()->toDateString(),
            ])))
            ->assertForbidden();
    }

    public function test_ringkasan_sesi_menampilkan_jumlah_status(): void
    {
        ['user' => $guru, 'schedule' => $schedule, 'students' => $students, 'date' => $date] = $this->scenario(4);

        $statuses = ['hadir', 'hadir', 'izin', 'alpa'];

        $payload = [];

        foreach ($students->values() as $index => $student) {
            $payload[$student->id] = ['status' => $statuses[$index]];
        }

        $this->actingAs($guru)->post(route('guru.attendance.store', $schedule), [
            'session_date' => $date->toDateString(),
            'attendances' => $payload,
        ]);

        $session = AttendanceSession::query()->firstOrFail();

        $this->assertSame(
            ['hadir' => 2, 'izin' => 1, 'sakit' => 0, 'alpa' => 1],
            $session->countsByStatus()
        );

        $this->actingAs($guru)
            ->get(route('guru.attendance.show', $session))
            ->assertOk()
            ->assertSee('Hadir')
            ->assertSee('Alpa');
    }
}
