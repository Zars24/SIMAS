<?php

namespace Tests\Feature\Siswa;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPortalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{student: Student, user: User, class: SchoolClass, teacher: Teacher}
     */
    private function classWithTeacher(): array
    {
        $teacherUser = User::factory()->guru()->create();
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
        $class = SchoolClass::factory()->create(['homeroom_teacher_id' => $teacher->id]);

        return ['class' => $class, 'teacher' => $teacher];
    }

    private function enrolledStudent(SchoolClass $class, array $userAttributes = []): Student
    {
        $user = User::factory()->siswa()->create($userAttributes);

        return Student::factory()->inClass($class)->create(['user_id' => $user->id]);
    }

    private function attendanceFor(Student $student, string $status, string $date, string $subjectName, ?SchoolClass $class = null): Attendance
    {
        $schedule = Schedule::factory()->create([
            'class_id' => ($class ?? $student->schoolClass)->id,
            'subject_id' => Subject::factory()->create(['name' => $subjectName])->id,
        ]);
        $session = AttendanceSession::factory()->create([
            'schedule_id' => $schedule->id,
            'session_date' => $date,
        ]);

        return Attendance::factory()->create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => $status,
        ]);
    }

    public function test_siswa_hanya_melihat_riwayat_absensinya_sendiri(): void
    {
        ['class' => $class] = $this->classWithTeacher();
        $kelasLain = SchoolClass::factory()->create();

        $aku = $this->enrolledStudent($class, ['name' => 'Siswa Sendiri']);
        $lain = $this->enrolledStudent($kelasLain, ['name' => 'Siswa Lain']);

        $this->attendanceFor($aku, 'hadir', now()->toDateString(), 'Matematika');
        $this->attendanceFor($lain, 'alpa', now()->toDateString(), 'Biologi', $kelasLain);

        $response = $this->actingAs($aku->user)->get(route('siswa.attendance'))->assertOk();

        $response->assertSee('Matematika');
        $response->assertDontSee('Biologi');
        $response->assertDontSee('Siswa Lain');
    }

    public function test_query_riwayat_dibatasi_ke_siswa_yang_login_bukan_parameter_url(): void
    {
        ['class' => $class] = $this->classWithTeacher();
        $kelasLain = SchoolClass::factory()->create();

        $aku = $this->enrolledStudent($class);
        $lain = $this->enrolledStudent($kelasLain);

        $this->attendanceFor($lain, 'alpa', now()->toDateString(), 'Fisika', $kelasLain);

        // Mencoba menyuntikkan student_id milik siswa lain lewat query string.
        $this->actingAs($aku->user)
            ->get(route('siswa.attendance', ['student_id' => $lain->id, 'status' => 'alpa']))
            ->assertOk()
            ->assertDontSee('Fisika');

        $this->assertSame(0, $aku->attendances()->count());
    }

    public function test_siswa_tidak_memiliki_endpoint_untuk_mengubah_absensi(): void
    {
        ['class' => $class] = $this->classWithTeacher();
        $aku = $this->enrolledStudent($class);

        // Tidak ada route siswa untuk membuat/mengubah absensi.
        $this->actingAs($aku->user)->post('/siswa/attendance')->assertStatus(405);
        $this->actingAs($aku->user)->delete('/siswa/attendance')->assertStatus(405);
    }

    public function test_ringkasan_dan_persentase_dihitung_dari_catatan(): void
    {
        ['class' => $class] = $this->classWithTeacher();
        $aku = $this->enrolledStudent($class);

        $this->attendanceFor($aku, 'hadir', now()->toDateString(), 'Kimia');
        $this->attendanceFor($aku, 'hadir', now()->subDay()->toDateString(), 'Sejarah');
        $this->attendanceFor($aku, 'hadir', now()->subDays(2)->toDateString(), 'Geografi');
        $this->attendanceFor($aku, 'izin', now()->subDays(3)->toDateString(), 'Ekonomi');

        $summary = $aku->attendanceSummary();

        $this->assertSame(3, $summary['hadir']);
        $this->assertSame(1, $summary['izin']);
        $this->assertSame(75.0, $aku->attendancePercentage());

        $this->actingAs($aku->user)
            ->get(route('siswa.dashboard'))
            ->assertOk()
            ->assertSee('75');
    }

    public function test_tanpa_catatan_menampilkan_belum_ada_data(): void
    {
        ['class' => $class] = $this->classWithTeacher();
        $aku = $this->enrolledStudent($class);

        $this->assertNull($aku->attendancePercentage());

        $this->actingAs($aku->user)
            ->get(route('siswa.attendance'))
            ->assertOk()
            ->assertSee('Belum ada data');
    }

    public function test_filter_status_dan_bulan_bekerja(): void
    {
        ['class' => $class] = $this->classWithTeacher();
        $kelasLain = SchoolClass::factory()->create();
        $aku = $this->enrolledStudent($class);

        $this->attendanceFor($aku, 'alpa', now()->toDateString(), 'Pendidikan Agama');
        $this->attendanceFor($aku, 'hadir', now()->toDateString(), 'Seni Budaya', $kelasLain);

        $this->actingAs($aku->user)
            ->get(route('siswa.attendance', ['status' => 'alpa']))
            ->assertOk()
            ->assertSee('Pendidikan Agama')
            ->assertDontSee('Seni Budaya');

        // Bulan tanpa data.
        $this->actingAs($aku->user)
            ->get(route('siswa.attendance', ['month' => '2020-01']))
            ->assertOk()
            ->assertSee('Belum ada riwayat absensi');
    }

    public function test_siswa_dapat_melihat_jadwal_kelasnya(): void
    {
        ['class' => $class] = $this->classWithTeacher();
        $aku = $this->enrolledStudent($class);

        Schedule::factory()->create([
            'class_id' => $class->id,
            'subject_id' => Subject::factory()->create(['name' => 'Bahasa Indonesia'])->id,
            'day_of_week' => 'monday',
        ]);

        $this->actingAs($aku->user)
            ->get(route('siswa.schedule'))
            ->assertOk()
            ->assertSee('Bahasa Indonesia');
    }

    public function test_siswa_dapat_melihat_profilnya_sendiri(): void
    {
        ['class' => $class] = $this->classWithTeacher();
        $aku = $this->enrolledStudent($class, ['name' => 'Nama Siswa Portal']);

        $this->actingAs($aku->user)
            ->get(route('siswa.profile'))
            ->assertOk()
            ->assertSee('Nama Siswa Portal')
            ->assertSee($aku->student_number);
    }
}
