<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->siswa(),
            'class_id' => null,
            'student_number' => $this->faker->unique()->numerify('2026####'),
            'birth_date' => $this->faker->dateTimeBetween('-18 years', '-15 years')->format('Y-m-d'),
            'phone' => $this->faker->numerify('08##########'),
            'address' => $this->faker->address(),
        ];
    }

    public function inClass(SchoolClass $schoolClass): static
    {
        return $this->state(fn () => ['class_id' => $schoolClass->id]);
    }
}
