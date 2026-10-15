<?php

namespace App\Http\Requests\Admin;

use App\Models\Schedule;
use App\Services\ScheduleConflictChecker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduleRequest extends FormRequest
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
            'class_id' => ['required', 'integer', Rule::exists('classes', 'id')],
            'teacher_id' => ['required', 'integer', Rule::exists('teachers', 'id')],
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
            'day_of_week' => ['required', Rule::in(array_keys(Schedule::DAYS))],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $conflicts = app(ScheduleConflictChecker::class)->conflicts($this->validated());

            foreach ($conflicts as $field => $message) {
                $validator->errors()->add($field, $message);
            }
        });
    }
}
