<?php

namespace App\Http\Requests\Admin;

use App\Models\SchoolClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'academic_year' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'homeroom_teacher_id' => ['nullable', 'integer', Rule::exists('teachers', 'id')],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'academic_year.regex' => 'Format tahun ajaran harus seperti 2025/2026.',
        ];
    }

    public function withValidator($validator): void
    {
        $schoolClass = $this->route('schoolClass');

        $validator->after(function ($validator) use ($schoolClass) {
            // Kombinasi nama + tahun ajaran harus unik.
            $duplicate = SchoolClass::query()
                ->where('name', $this->input('name'))
                ->where('academic_year', $this->input('academic_year'))
                ->whereKeyNot($schoolClass->getKey())
                ->exists();

            if ($duplicate) {
                $validator->errors()->add(
                    'name',
                    'Kelas dengan nama dan tahun ajaran tersebut sudah ada.'
                );
            }

            $teacherId = $this->input('homeroom_teacher_id');

            if (! $teacherId) {
                return;
            }

            $teacherTaken = SchoolClass::query()
                ->where('homeroom_teacher_id', $teacherId)
                ->where('academic_year', $this->input('academic_year'))
                ->whereKeyNot($schoolClass->getKey())
                ->exists();

            if ($teacherTaken) {
                $validator->errors()->add(
                    'homeroom_teacher_id',
                    'Guru tersebut sudah menjadi wali kelas lain pada tahun ajaran ini.'
                );
            }
        });
    }
}
