<?php

namespace Tests\Feature\Auth;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function guru(): User
    {
        $user = User::factory()->guru()->create();
        Teacher::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    private function siswa(): User
    {
        $user = User::factory()->siswa()->create();
        Student::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        foreach (['/admin/dashboard', '/guru/dashboard', '/siswa/dashboard'] as $path) {
            $this->get($path)->assertRedirect(route('login'));
        }

        $this->assertGuest();
    }

    public function test_siswa_tidak_dapat_mengakses_area_admin(): void
    {
        $this->actingAs($this->siswa())
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->actingAs($this->siswa())
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_siswa_tidak_dapat_mengakses_area_guru(): void
    {
        $this->actingAs($this->siswa())
            ->get(route('guru.dashboard'))
            ->assertForbidden();

        $this->actingAs($this->siswa())
            ->get(route('guru.attendance.index'))
            ->assertForbidden();
    }

    public function test_guru_tidak_dapat_mengakses_area_admin(): void
    {
        $this->actingAs($this->guru())
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($this->guru())
            ->get(route('admin.students.index'))
            ->assertForbidden();
    }

    public function test_admin_tidak_dapat_mengakses_area_guru_dan_siswa(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('guru.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('siswa.dashboard'))->assertForbidden();
    }

    public function test_siswa_tidak_dapat_mengirim_absensi(): void
    {
        $siswa = $this->siswa();
        $schedule = \App\Models\Schedule::factory()->create();
        $session = \App\Models\AttendanceSession::factory()->create([
            'schedule_id' => $schedule->id,
        ]);

        // Tidak ada endpoint absensi untuk siswa: semua ditolak di level route/middleware.
        $this->actingAs($siswa)
            ->post(route('guru.attendance.store', $schedule))
            ->assertForbidden();

        $this->actingAs($siswa)
            ->put(route('guru.attendance.update', $session))
            ->assertForbidden();

        $this->actingAs($siswa)
            ->get(route('guru.attendance.fill', ['schedule' => $schedule, 'date' => now()->toDateString()]))
            ->assertForbidden();

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_halaman_error_403_dan_404_tersedia(): void
    {
        $this->actingAs($this->siswa())
            ->get('/admin/dashboard')
            ->assertForbidden()
            ->assertSee('403', false);

        $this->actingAs(User::factory()->admin()->create())
            ->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('404', false);
    }
}
