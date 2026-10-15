<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Data sekolah demo yang deterministik (mt_srand) dan non-sensitif.
 */
class DemoSchoolSeeder extends Seeder
{
    private const ACADEMIC_YEAR = '2025/2026';

    private const PASSWORD = 'password';

    /** Guru & kelas yang diampu. */
    private array $teacherMap = [];

    /** @var array<string, Subject> */
    private array $subjectMap = [];

    public function run(): void
    {
        mt_srand(20260101);

        $this->seedTeachers();
        $this->seedSubjects();
        $classes = $this->seedClasses();
        $this->seedStudents($classes);
        $this->seedSchedules($classes);
        $this->seedAttendance();

        AuditLogger::log('system.seeded', null, [
            'teachers' => Teacher::count(),
            'students' => Student::count(),
            'classes' => SchoolClass::count(),
            'schedules' => Schedule::count(),
            'attendance_sessions' => AttendanceSession::count(),
            'attendances' => Attendance::count(),
        ]);
    }

    private function seedTeachers(): void
    {
        $teachers = [
            ['Budi Santoso', 'budi.santoso', 'G-001', '081200000101', 'Jl. Kenanga No. 12, Kota Bukit Hijau'],
            ['Siti Rahayu', 'siti.rahayu', 'G-002', '081200000102', 'Jl. Melati No. 3, Kota Bukit Hijau'],
            ['Ahmad Fauzi', 'ahmad.fauzi', 'G-003', '081200000103', 'Jl. Anggrek No. 8, Kota Bukit Hijau'],
            ['Dewi Lestari', 'dewi.lestari', 'G-004', '081200000104', 'Jl. Mawar No. 21, Kota Bukit Hijau'],
            ['Joko Prasetyo', 'joko.prasetyo', 'G-005', '081200000105', 'Jl. Dahlia No. 5, Kota Bukit Hijau'],
            ['Rina Kartika', 'rina.kartika', 'G-006', '081200000106', 'Jl. Kamboja No. 17, Kota Bukit Hijau'],
            ['Hendra Wijaya', 'hendra.wijaya', 'G-007', '081200000107', 'Jl. Flamboyan No. 9, Kota Bukit Hijau'],
            ['Maya Anggraini', 'maya.anggraini', 'G-008', '081200000108', 'Jl. Cempaka No. 14, Kota Bukit Hijau'],
            ['Agus Salim', 'agus.salim', 'G-009', '081200000109', 'Jl. Pahlawan No. 2, Kota Bukit Hijau'],
        ];

        foreach ($teachers as [$name, $username, $number, $phone, $address]) {
            $user = User::updateOrCreate(
                ['email' => $username.'@simas.test'],
                [
                    'name' => $name,
                    'username' => $username,
                    'password' => self::PASSWORD,
                    'role' => User::ROLE_GURU,
                    'is_active' => true,
                ]
            );

            $teacher = Teacher::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'teacher_number' => $number,
                    'phone' => $phone,
                    'address' => $address,
                ]
            );

            $this->teacherMap[$username] = $teacher;
        }
    }

    private function seedSubjects(): void
    {
        $subjects = [
            ['MTK', 'Matematika', 'Aljabar, geometri, dan statistika dasar.'],
            ['BIN', 'Bahasa Indonesia', 'Membaca, menulis, dan sastra.'],
            ['BIG', 'Bahasa Inggris', 'Listening, speaking, reading, writing.'],
            ['FIS', 'Fisika', 'Mekanika, listrik, dan gelombang.'],
            ['KIM', 'Kimia', 'Struktur materi dan reaksi kimia.'],
            ['BIO', 'Biologi', 'Sel, ekosistem, dan genetika.'],
            ['SEJ', 'Sejarah', 'Sejarah Indonesia dan dunia.'],
            ['EKO', 'Ekonomi', 'Mikro, makro, dan akuntansi dasar.'],
            ['INF', 'Informatika', 'Logika pemrograman dan literasi digital.'],
            ['PJOK', 'PJOK', 'Pendidikan jasmani, olahraga, dan kesehatan.'],
        ];

        foreach ($subjects as [$code, $name, $description]) {
            $this->subjectMap[$code] = Subject::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'description' => $description]
            );
        }
    }

    /**
     * @return array<int, SchoolClass>
     */
    private function seedClasses(): array
    {
        $classes = [
            ['X-1', 'siti.rahayu'],
            ['X-2', 'ahmad.fauzi'],
            ['XI-1', 'joko.prasetyo'],
        ];

        $result = [];

        foreach ($classes as [$name, $homeroomUsername]) {
            $result[] = SchoolClass::updateOrCreate(
                ['name' => $name, 'academic_year' => self::ACADEMIC_YEAR],
                [
                    'homeroom_teacher_id' => $this->teacherMap[$homeroomUsername]->id,
                    'is_active' => true,
                ]
            );
        }

        return $result;
    }

    /**
     * @param  array<int, SchoolClass>  $classes
     */
    private function seedStudents(array $classes): void
    {
        $maleFirstNames = ['Andi', 'Bagas', 'Cahyo', 'Dimas', 'Eko', 'Fajar', 'Gilang', 'Hafiz', 'Ilham', 'Rizky'];
        $femaleFirstNames = ['Ayu', 'Bella', 'Citra', 'Dinda', 'Erika', 'Fitri', 'Gita', 'Hana', 'Indah', 'Jihan'];
        $lastNames = ['Pratama', 'Saputra', 'Wijaya', 'Hidayat', 'Nugroho', 'Ramadhan', 'Santoso', 'Anggraini', 'Kusuma', 'Maharani'];

        $counter = 1;
        $usedNames = [];

        foreach ($classes as $classIndex => $class) {
            for ($i = 0; $i < 12; $i++) {
                $gender = ($i % 2 === 0) ? 'male' : 'female';
                $first = $gender === 'male'
                    ? $maleFirstNames[($classIndex * 4 + intdiv($i, 2)) % count($maleFirstNames)]
                    : $femaleFirstNames[($classIndex * 4 + intdiv($i, 2)) % count($femaleFirstNames)];

                $last = $lastNames[($classIndex * 3 + $i) % count($lastNames)];

                $name = $first.' '.$last;

                if (in_array($name, $usedNames, true)) {
                    $last = $lastNames[($classIndex * 3 + $i + 5) % count($lastNames)];
                    $name = $first.' '.$last;
                }

                $usedNames[] = $name;

                $username = Str::slug($first.'.'.$last, '.');
                $username = Str::lower($username);

                if (User::query()->where('username', $username)->exists()) {
                    $username .= $counter;
                }

                $birthYear = $class->name === 'XI-1' ? 2008 : 2009;

                $user = User::updateOrCreate(
                    ['email' => $username.'@siswa.simas.test'],
                    [
                        'name' => $name,
                        'username' => $username,
                        'password' => self::PASSWORD,
                        'role' => User::ROLE_SISWA,
                        'is_active' => true,
                    ]
                );

                Student::updateOrCreate(
                    ['student_number' => '2025'.str_pad((string) $counter, 3, '0', STR_PAD_LEFT)],
                    [
                        'user_id' => $user->id,
                        'class_id' => $class->id,
                        'birth_date' => sprintf(
                            '%d-%02d-%02d',
                            $birthYear,
                            mt_rand(1, 12),
                            mt_rand(1, 28)
                        ),
                        'phone' => '08120000'.str_pad((string) $counter, 4, '0', STR_PAD_LEFT),
                        'address' => 'Jl. Pelajar No. '.mt_rand(1, 60).', Kota Bukit Hijau',
                    ]
                );

                $counter++;
            }
        }
    }

    /**
     * @param  array<int, SchoolClass>  $classes
     */
    private function seedSchedules(array $classes): void
    {
        $subjectTeachers = [
            'MTK' => 'budi.santoso',
            'BIN' => 'siti.rahayu',
            'BIG' => 'ahmad.fauzi',
            'FIS' => 'dewi.lestari',
            'KIM' => 'dewi.lestari',
            'BIO' => 'maya.anggraini',
            'SEJ' => 'joko.prasetyo',
            'EKO' => 'agus.salim',
            'INF' => 'rina.kartika',
            'PJOK' => 'hendra.wijaya',
        ];

        $periods = [
            ['07:00', '07:45'],
            ['07:45', '08:30'],
            ['08:45', '09:30'],
            ['09:30', '10:15'],
        ];

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $pool = array_keys($subjectTeachers);
        $busy = [];

        foreach ($classes as $classIndex => $class) {
            foreach ($days as $dayIndex => $day) {
                $periodCount = $day === 'friday' ? 3 : 4;

                for ($period = 0; $period < $periodCount; $period++) {
                    $slot = $day.'|'.$period;

                    for ($k = 0; $k < count($pool); $k++) {
                        $code = $pool[($classIndex * 3 + $dayIndex * 2 + $period + $k) % count($pool)];
                        $teacherUsername = $subjectTeachers[$code];

                        if (isset($busy[$slot][$teacherUsername])) {
                            continue;
                        }

                        $busy[$slot][$teacherUsername] = true;

                        Schedule::updateOrCreate(
                            [
                                'class_id' => $class->id,
                                'day_of_week' => $day,
                                'start_time' => $periods[$period][0],
                            ],
                            [
                                'teacher_id' => $this->teacherMap[$teacherUsername]->id,
                                'subject_id' => $this->subjectMap[$code]->id,
                                'end_time' => $periods[$period][1],
                                'is_active' => true,
                            ]
                        );

                        break;
                    }
                }
            }
        }
    }

    private function seedAttendance(): void
    {
        // Ambil 15 hari sekolah terakhir (Senin–Jumat) sampai hari ini.
        $schoolDays = [];
        $cursor = Carbon::today();

        while (count($schoolDays) < 15) {
            if (! $cursor->isWeekend()) {
                $schoolDays[] = $cursor->copy();
            }

            $cursor->subDay();
        }

        $schedules = Schedule::query()
            ->with(['teacher.user', 'schoolClass'])
            ->where('is_active', true)
            ->get();

        $studentsByClass = [];

        foreach ($schedules as $schedule) {
            $classId = $schedule->class_id;

            if (! isset($studentsByClass[$classId])) {
                $studentsByClass[$classId] = Student::query()
                    ->where('class_id', $classId)
                    ->whereHas('user', fn ($query) => $query->active())
                    ->orderBy('student_number')
                    ->get();
            }

            $students = $studentsByClass[$classId];

            foreach ($schoolDays as $date) {
                if (strtolower($date->format('l')) !== $schedule->day_of_week) {
                    continue;
                }

                $exists = AttendanceSession::query()
                    ->where('schedule_id', $schedule->id)
                    ->whereDate('session_date', $date)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $session = AttendanceSession::create([
                    'schedule_id' => $schedule->id,
                    'session_date' => $date->toDateString(),
                    'topic' => null,
                    'created_by' => $schedule->teacher->user_id,
                    'submitted_at' => $date->copy()->setTime(13, 0),
                ]);

                $rows = [];
                $timestamp = $date->copy()->setTime(13, 0)->toDateTimeString();

                foreach ($students as $student) {
                    $roll = mt_rand(1, 100);

                    if ($roll <= 85) {
                        $status = Attendance::STATUS_HADIR;
                    } elseif ($roll <= 90) {
                        $status = Attendance::STATUS_IZIN;
                    } elseif ($roll <= 95) {
                        $status = Attendance::STATUS_SAKIT;
                    } else {
                        $status = Attendance::STATUS_ALPA;
                    }

                    $note = null;

                    if ($status === Attendance::STATUS_SAKIT && mt_rand(1, 3) === 1) {
                        $note = 'Surat keterangan dokter.';
                    } elseif ($status === Attendance::STATUS_IZIN && mt_rand(1, 3) === 1) {
                        $note = 'Keperluan keluarga.';
                    } elseif ($status === Attendance::STATUS_ALPA && mt_rand(1, 2) === 1) {
                        $note = 'Tanpa keterangan.';
                    }

                    $rows[] = [
                        'attendance_session_id' => $session->id,
                        'student_id' => $student->id,
                        'status' => $status,
                        'note' => $note,
                        'recorded_by' => $schedule->teacher->user_id,
                        'recorded_at' => $timestamp,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }

                foreach (array_chunk($rows, 500) as $chunk) {
                    DB::table('attendances')->insert($chunk);
                }
            }
        }
    }
}
