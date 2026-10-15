<?php

namespace Tests\Feature\Admin;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_dapat_membuat_guru_beserta_akunnya(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.teachers.store'), [
            'name' => 'Ibu Rina',
            'username' => 'rina_guru',
            'email' => 'rina@example.test',
            'password' => 'rahasia123',
            'teacher_number' => 'G-999',
            'phone' => '081200000000',
            'address' => 'Jl. Melati 1',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.teachers.index'));

        $this->assertDatabaseHas('users', ['email' => 'rina@example.test', 'role' => 'guru']);
        $this->assertDatabaseHas('teachers', ['teacher_number' => 'G-999']);
    }

    public function test_username_dan_email_duplikat_ditolak(): void
    {
        User::factory()->guru()->create(['username' => 'bentrok', 'email' => 'bentrok@example.test']);

        $this->actingAs($this->admin())
            ->post(route('admin.teachers.store'), [
                'name' => 'Guru Baru',
                'username' => 'bentrok',
                'email' => 'bentrok@example.test',
                'password' => 'rahasia123',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors(['username', 'email']);

        $this->assertDatabaseCount('teachers', 0);
    }

    public function test_admin_dapat_membuat_siswa_dan_menempatkannya_di_kelas(): void
    {
        $class = SchoolClass::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.students.store'), [
                'name' => 'Dewi Lestari',
                'username' => 'dewi_siswa',
                'email' => 'dewi@example.test',
                'password' => 'rahasia123',
                'student_number' => 'S-2026999',
                'class_id' => $class->id,
                'birth_date' => '2010-05-01',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('students', [
            'student_number' => 'S-2026999',
            'class_id' => $class->id,
        ]);
    }

    public function test_kelas_tidak_dapat_dihapus_bila_masih_memiliki_siswa(): void
    {
        $class = SchoolClass::factory()->create();
        Student::factory()->inClass($class)->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.classes.destroy', $class))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('classes', ['id' => $class->id]);
    }

    public function test_mata_pelajaran_yang_dipakai_jadwal_tidak_dapat_dihapus(): void
    {
        $subject = Subject::factory()->create();
        \App\Models\Schedule::factory()->create(['subject_id' => $subject->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.subjects.destroy', $subject))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('subjects', ['id' => $subject->id]);
    }

    public function test_jadwal_bentrok_guru_ditolak(): void
    {
        $teacher = Teacher::factory()->create();
        $classA = SchoolClass::factory()->create();
        $classB = SchoolClass::factory()->create();
        $subject = Subject::factory()->create();

        \App\Models\Schedule::factory()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $classA->id,
            'subject_id' => $subject->id,
            'day_of_week' => 'monday',
            'start_time' => '07:00',
            'end_time' => '08:30',
        ]);

        // Guru sama, jam yang bertabrakan, kelas berbeda.
        $this->actingAs($this->admin())
            ->post(route('admin.schedules.store'), [
                'class_id' => $classB->id,
                'teacher_id' => $teacher->id,
                'subject_id' => $subject->id,
                'day_of_week' => 'monday',
                'start_time' => '08:00',
                'end_time' => '09:00',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('teacher_id');

        $this->assertDatabaseCount('schedules', 1);
    }

    public function test_jam_selesai_harus_setelah_jam_mulai(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.schedules.store'), [
                'class_id' => SchoolClass::factory()->create()->id,
                'teacher_id' => Teacher::factory()->create()->id,
                'subject_id' => Subject::factory()->create()->id,
                'day_of_week' => 'tuesday',
                'start_time' => '10:00',
                'end_time' => '09:00',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('end_time');

        $this->assertDatabaseCount('schedules', 0);
    }

    public function test_admin_utama_tidak_dapat_dihapus_atau_dinonaktifkan(): void
    {
        $admin = $this->admin();
        $primary = User::factory()->admin()->create([
            'email' => config('simas.primary_admin_email'),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $primary))
            ->assertSessionHas('error');

        $this->actingAs($admin)
            ->patch(route('admin.users.toggle-active', $primary))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $primary->id, 'is_active' => true]);
    }

    public function test_admin_tidak_dapat_menghapus_akunnya_sendiri(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_dapat_menonaktifkan_akun_lain(): void
    {
        $admin = $this->admin();
        $target = User::factory()->guru()->create();
        Teacher::factory()->create(['user_id' => $target->id]);

        $this->actingAs($admin)
            ->patch(route('admin.users.toggle-active', $target))
            ->assertSessionHas('success');

        $this->assertFalse($target->fresh()->is_active);
    }
}
