<?php

namespace Tests\Feature\Auth;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_login_dapat_dibuka_tamu(): void
    {
        $this->get('/masuk')->assertOk()->assertSee('Masuk', false);
    }

    public function test_pengguna_dapat_login_dengan_email(): void
    {
        $user = User::factory()->admin()->create(['email' => 'admin@example.test']);

        $response = $this->post('/masuk', [
            'login' => 'admin@example.test',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_pengguna_dapat_login_dengan_username(): void
    {
        $user = User::factory()->guru()->create(['username' => 'guru.budi']);
        Teacher::factory()->create(['user_id' => $user->id]);

        $this->post('/masuk', [
            'login' => 'guru.budi',
            'password' => 'password',
        ])->assertRedirect(route('guru.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_kata_sandi_salah_ditolak(): void
    {
        User::factory()->create(['email' => 'user@example.test']);

        $this->post('/masuk', [
            'login' => 'user@example.test',
            'password' => 'salah-sekali',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_akun_nonaktif_tidak_dapat_login(): void
    {
        User::factory()->inactive()->create(['email' => 'nonaktif@example.test']);

        $this->post('/masuk', [
            'login' => 'nonaktif@example.test',
            'password' => 'password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_akun_nonaktif_yang_sedang_login_dipaksa_keluar(): void
    {
        $user = User::factory()->siswa()->create();
        Student::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get(route('siswa.dashboard'))->assertOk();

        // Admin menonaktifkan akun saat pengguna masih memegang sesi.
        $user->update(['is_active' => false]);

        $this->actingAs($user->fresh())
            ->get(route('siswa.dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_pengguna_dapat_logout(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->post('/keluar')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_redirect_dashboard_sesuai_role(): void
    {
        $admin = User::factory()->admin()->create();
        $guru = User::factory()->guru()->create();
        Teacher::factory()->create(['user_id' => $guru->id]);
        $siswa = User::factory()->siswa()->create();
        Student::factory()->create(['user_id' => $siswa->id]);

        $this->actingAs($admin)->get('/dashboard')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($guru)->get('/dashboard')->assertRedirect(route('guru.dashboard'));
        $this->actingAs($siswa)->get('/dashboard')->assertRedirect(route('siswa.dashboard'));
    }

    public function test_percobaan_login_dibatasi(): void
    {
        User::factory()->create(['email' => 'target@example.test']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/masuk', [
                'login' => 'target@example.test',
                'password' => 'salah',
            ]);
        }

        $this->post('/masuk', [
            'login' => 'target@example.test',
            'password' => 'password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }
}
