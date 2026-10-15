<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AttendanceStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    /**
     * @return array{teacher: Teacher, class: SchoolClass, subject: Subject, schedule: Schedule, students: \Illuminate\Support\Collection<int, Student>}
     */
    private function school(int $studentCount = 2): array
    {
        $teacherUser = User::factory()->guru()->create(['name' => 'Pak Guru Uji']);
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
        $class = SchoolClass::factory()->create(['name' => 'XI IPA 9', 'academic_year' => '2025/2026']);
        $subject = Subject::factory()->create(['name' => 'Matematika Lanjut']);

        $schedule = Schedule::factory()->create([
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
        ]);

        $students = Student::factory()->count($studentCount)->inClass($class)->create();

        return compact('teacher', 'class', 'subject', 'schedule', 'students');
    }

    private function seedAttendances(array $ctx, array $statuses, string $date): AttendanceSession
    {
        $session = AttendanceSession::factory()->create([
            'schedule_id' => $ctx['schedule']->id,
            'session_date' => $date,
        ]);

        foreach ($ctx['students']->values() as $index => $student) {
            Attendance::factory()->create([
                'attendance_session_id' => $session->id,
                'student_id' => $student->id,
                'status' => $statuses[$index] ?? 'hadir',
            ]);
        }

        return $session;
    }

    public function test_dashboard_admin_menampilkan_angka_dari_database(): void
    {
        $ctx = $this->school(3);
        $this->seedAttendances($ctx, ['hadir', 'izin', 'alpa'], now()->toDateString());

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Siswa Aktif')
            ->assertSee('Guru Aktif')
            ->assertSee('Kelas Aktif');
    }

    public function test_rekap_absensi_memfilter_berdasarkan_status(): void
    {
        $ctx = $this->school(2);
        $this->seedAttendances($ctx, ['hadir', 'alpa'], now()->toDateString());

        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.attendance.index', ['status' => 'alpa']))
            ->assertOk()
            ->assertSee($ctx['students']->last()->user->name)
            ->assertDontSee($ctx['students']->first()->user->name);

        $this->actingAs($admin)
            ->get(route('admin.attendance.index', ['status' => 'hadir']))
            ->assertOk()
            ->assertSee($ctx['students']->first()->user->name);
    }

    public function test_rekap_absensi_memfilter_berdasarkan_rentang_tanggal(): void
    {
        $ctx = $this->school(1);

        $this->seedAttendances($ctx, ['hadir'], now()->subDays(40)->toDateString());

        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.attendance.index', [
                'date_from' => now()->subDays(3)->toDateString(),
                'date_to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Tidak ada catatan absensi');

        $this->actingAs($admin)
            ->get(route('admin.attendance.index', [
                'date_from' => now()->subDays(60)->toDateString(),
                'date_to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee($ctx['students']->first()->user->name);
    }

    public function test_detail_sesi_absensi_menampilkan_status_per_siswa(): void
    {
        $ctx = $this->school(2);
        $session = $this->seedAttendances($ctx, ['sakit', 'hadir'], now()->toDateString());

        $this->actingAs($this->admin())
            ->get(route('admin.attendance.sessions.show', $session))
            ->assertOk()
            ->assertSee('Matematika Lanjut')
            ->assertSee('Sakit');
    }

    public function test_ekspor_csv_mengembalikan_berkas_dengan_header(): void
    {
        $ctx = $this->school(1);
        $this->seedAttendances($ctx, ['hadir'], now()->toDateString());

        $response = $this->actingAs($this->admin())->get(route('admin.attendance.export', [
            'date_from' => now()->subDays(3)->toDateString(),
            'date_to' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Tanggal', $csv);
        $this->assertStringContainsString('Nama Siswa', $csv);
        $this->assertStringContainsString($ctx['students']->first()->user->name, $csv);
        $this->assertStringContainsString('Hadir', $csv);
    }

    public function test_audit_log_terisi_saat_absensi_dibuat(): void
    {
        $ctx = $this->school(1);

        $guru = $ctx['teacher']->user;

        $this->actingAs($guru)->post(route('guru.attendance.store', $ctx['schedule']), [
            'session_date' => now()->toDateString(),
            'attendances' => $ctx['students']->mapWithKeys(fn (Student $s) => [$s->id => ['status' => 'hadir']])->all(),
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $guru->id,
            'action' => 'attendance.session_created',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.audit-logs.index'))
            ->assertOk()
            ->assertSee('attendance.session_created');
    }

    public function test_statistik_menghitung_persentase_dan_tren(): void
    {
        $stats = app(AttendanceStats::class);

        $this->assertNull($stats->percentage(['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0]));

        $this->assertSame(75.0, $stats->percentage(['hadir' => 3, 'izin' => 1, 'sakit' => 0, 'alpa' => 0]));

        $ctx = $this->school(2);
        $this->seedAttendances($ctx, ['hadir', 'alpa'], now()->toDateString());

        $counts = $stats->statusCounts(null, now()->startOfDay(), now()->endOfDay());

        $this->assertSame(1, $counts['hadir']);
        $this->assertSame(1, $counts['alpa']);
        $this->assertSame(50.0, $stats->percentage($counts));

        $trend = $stats->trendSeries(now()->subDays(6)->startOfDay(), now()->endOfDay());

        $this->assertCount(7, $trend['labels']);
        $this->assertCount(7, $trend['percentage']);
        $this->assertSame(50.0, end($trend['percentage']));
    }
}
