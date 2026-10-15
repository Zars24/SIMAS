<?php

namespace App\Http\Requests\Guru;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('session'));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'topic' => ['nullable', 'string', 'max:255'],
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*.status' => ['required', Rule::in(array_keys(Attendance::STATUSES))],
            'attendances.*.note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'attendances.*.status.required' => 'Semua siswa harus memiliki status absensi.',
            'attendances.*.status.in' => 'Status absensi tidak valid.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $session = $this->route('session');

            $expected = Student::query()
                ->where('class_id', $session->schedule->class_id)
                ->whereHas('user', fn ($query) => $query->where('is_active', true))
                ->pluck('id')
                ->map(fn ($id) => (int) $id);

            $submitted = collect(array_keys($this->input('attendances', [])))->map(fn ($id) => (int) $id);

            if ($submitted->diff($expected)->isNotEmpty()) {
                $validator->errors()->add('attendances', 'Terdapat data siswa yang bukan anggota kelas ini.');

                return;
            }

            // Untuk koreksi, baris yang belum pernah tercatat wajib dilengkapi;
            // baris lama yang statusnya tidak lagi dikirim akan dibiarkan apa adanya.
            $alreadyRecorded = $session->attendances()->pluck('student_id')->map(fn ($id) => (int) $id);
            $missing = $expected->diff($alreadyRecorded)->diff($submitted);

            if ($missing->isNotEmpty()) {
                $validator->errors()->add(
                    'attendances',
                    'Semua siswa kelas ini harus memiliki status absensi sebelum disimpan.'
                );
            }
        });
    }
}
