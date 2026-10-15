<?php

namespace Database\Factories;

use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttendanceSession>
 */
class AttendanceSessionFactory extends Factory
{
    protected $model = AttendanceSession::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'schedule_id' => Schedule::factory(),
            'session_date' => now()->toDateString(),
            'topic' => null,
            'created_by' => User::factory()->admin(),
            'submitted_at' => now(),
        ];
    }
}
