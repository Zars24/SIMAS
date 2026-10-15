<?php

namespace Tests\Feature\Performance;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Menjaga agar halaman daftar tetap memakai eager loading
 * dan tidak mengalami masalah N+1 saat jumlah data bertambah.
 */
class QueryCountTest extends TestCase
{
    use RefreshDatabase;

    /** Jumlah query yang dijalankan saat memuat sebuah halaman (setelah pemanasan). */
    private function queryCount(User $user, string $url): int
    {
        // Pemanasan: menghilangkan variasi cache/bootstrapping pada permintaan pertama.
        $this->actingAs($user)->get($url)->assertOk();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAs($user)->get($url)->assertOk();

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }

    /**
     * @return array{teacher: Teacher, guru: User, class: SchoolClass, subject: Subject}
     */
    private function school(): array
    {
        $guru = User::factory()->guru()->create();
        $teacher = Teacher::factory()->create(['user_id' => $guru->id]);
        $class = SchoolClass::factory()->create(['homeroom_teacher_id' => $teacher->id]);
        $subject = Subject::factory()->create();

        return compact('teacher', 'guru', 'class', 'subject');
    }

    private function scheduleFor(array $ctx, string $day): Schedule
    {
        return Schedule::factory()->create([
            'class_id' => $ctx['class']->id,
            'teacher_id' => $ctx['teacher']->id,
            'subject_id' => $ctx['subject']->id,
            'day_of_week' => $day,
        ]);
    }

    private function sessionWithStudents(Schedule $schedule, int $studentCount, string $date): AttendanceSession
    {
        $session = AttendanceSession::factory()->create([
            'schedule_id' => $schedule->id,
            'session_date' => $date,
        ]);

        $students = Student::factory()->count($studentCount)->inClass($schedule->schoolClass)->create();

        foreach ($students as $student) {
            Attendance::factory()->create([
                'attendance_session_id' => $session->id,
                'student_id' => $student->id,
            ]);
        }

        return $session;
    }

    public function test_dashboard_admin_tidak_bertambah_query_saat_data_bertambah(): void
    {
        $admin = User::factory()->admin()->create();
        $ctx = $this->school();

        // Beberapa kelas + siswa agar dashboard punya data untuk diagregasi.
        Student::factory()->count(3)->inClass($ctx['class'])->create();

        $kecil = $this->queryCount($admin, route('admin.dashboard'));

        // Tambah banyak data: jumlah query tidak boleh ikut bertambah.
        $kelasTambahan = SchoolClass::factory()->count(4)->create();

        foreach ($kelasTambahan as $class) {
            Student::factory()->count(8)->inClass($class)->create();
            Teacher::factory()->count(2)->create();
        }

        $besar = $this->queryCount($admin, route('admin.dashboard'));

        $this->assertSame(
            $kecil,
            $besar,
            "Jumlah query dashboard admin berubah dari {$kecil} menjadi {$besar} setelah data bertambah (indikasi N+1)."
        );
    }

    public function test_rekap_absensi_admin_tidak_mengalami_n_plus_one(): void
    {
        $admin = User::factory()->admin()->create();
        $ctx = $this->school();
        $schedule = $this->scheduleFor($ctx, 'monday');

        $this->sessionWithStudents($schedule, 3, now()->toDateString());

        $kecil = $this->queryCount($admin, route('admin.attendance.index'));

        // Sesi kedua dengan jumlah siswa jauh lebih banyak.
        $scheduleDua = $this->scheduleFor($ctx, 'tuesday');
        $this->sessionWithStudents($scheduleDua, 15, now()->subDay()->toDateString());

        $besar = $this->queryCount($admin, route('admin.attendance.index'));

        $this->assertSame(
            $kecil,
            $besar,
            "Jumlah query rekap absensi berubah dari {$kecil} menjadi {$besar} (indikasi N+1)."
        );
    }

    public function test_halaman_sesi_guru_tidak_mengalami_n_plus_one(): void
    {
        $ctx = $this->school();
        $schedule = $this->scheduleFor($ctx, 'monday');

        $session = $this->sessionWithStudents($schedule, 3, now()->toDateString());

        $kecil = $this->queryCount($ctx['guru'], route('guru.attendance.show', $session));

        // Sesi lain dengan 12 siswa: halaman detail harus tetap memakai eager loading.
        $scheduleDua = $this->scheduleFor($ctx, 'wednesday');
        $sessionDua = $this->sessionWithStudents($scheduleDua, 12, now()->subDay()->toDateString());

        $besar = $this->queryCount($ctx['guru'], route('guru.attendance.show', $sessionDua));

        $this->assertSame(
            $kecil,
            $besar,
            "Jumlah query halaman sesi guru berubah dari {$kecil} menjadi {$besar} (indikasi N+1)."
        );
    }

    public function test_riwayat_absensi_siswa_tidak_mengalami_n_plus_one(): void
    {
        $ctx = $this->school();

        $siswaUser = User::factory()->siswa()->create();
        $siswa = Student::factory()->inClass($ctx['class'])->create(['user_id' => $siswaUser->id]);

        // Satu catatan awal milik siswa yang login (halaman tidak kosong).
        $sessionAwal = AttendanceSession::factory()->create([
            'schedule_id' => $this->scheduleFor($ctx, 'monday')->id,
            'session_date' => now()->toDateString(),
        ]);

        Attendance::factory()->create([
            'attendance_session_id' => $sessionAwal->id,
            'student_id' => $siswa->id,
        ]);

        $kecil = $this->queryCount($siswaUser, route('siswa.attendance'));

        // Tambah banyak catatan untuk siswa yang sama: jumlah query harus tetap.
        foreach (['tuesday', 'wednesday', 'thursday', 'friday'] as $index => $day) {
            $session = AttendanceSession::factory()->create([
                'schedule_id' => $this->scheduleFor($ctx, $day)->id,
                'session_date' => now()->subDays($index + 1)->toDateString(),
            ]);

            Attendance::factory()->create([
                'attendance_session_id' => $session->id,
                'student_id' => $siswa->id,
            ]);
        }

        $besar = $this->queryCount($siswaUser, route('siswa.attendance'));

        $this->assertSame(
            $kecil,
            $besar,
            "Jumlah query riwayat siswa berubah dari {$kecil} menjadi {$besar} (indikasi N+1)."
        );
    }

    public function test_daftar_siswa_admin_tidak_mengalami_n_plus_one(): void
    {
        $admin = User::factory()->admin()->create();
        $ctx = $this->school();

        Student::factory()->count(25)->inClass($ctx['class'])->create();
        $kecil = $this->queryCount($admin, route('admin.students.index', ['page' => 1]));

        // Halaman kedua (pagination) harus memakai jumlah query yang sama.
        $besar = $this->queryCount($admin, route('admin.students.index', ['page' => 2]));

        $this->assertSame(
            $kecil,
            $besar,
            "Jumlah query daftar siswa berubah dari {$kecil} menjadi {$besar} (indikasi N+1)."
        );
    }
}
