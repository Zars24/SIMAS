<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassRequest extends FormRequest
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
        $validator->after(function ($validator) {
            $teacherId = $this->input('homeroom_teacher_id');

            if (! $teacherId) {
                return;
            }

            $exists = \App\Models\SchoolClass::query()
                ->where('homeroom_teacher_id', $teacherId)
                ->where('academic_year', $this->input('academic_year'))
                ->exists();

            if ($exists) {
                $validator->errors()->add(
                    'homeroom_teacher_id',
                    'Guru tersebut sudah menjadi wali kelas lain pada tahun ajaran ini.'
                );
            }
        });
    }
}
