<?php

namespace App\Http\Requests\Guru;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('fill', $this->route('schedule'));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'session_date' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
                'after_or_equal:'.now()->subDays(90)->toDateString(),
            ],
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
            'session_date.before_or_equal' => 'Tanggal sesi tidak boleh di masa depan.',
            'session_date.after_or_equal' => 'Tanggal sesi terlalu jauh di masa lalu (maksimal 90 hari).',
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

            $schedule = $this->route('schedule');

            $expected = Student::query()
                ->where('class_id', $schedule->class_id)
                ->whereHas('user', fn ($query) => $query->where('is_active', true))
                ->pluck('id')
                ->map(fn ($id) => (int) $id);

            $submitted = collect(array_keys($this->input('attendances', [])))->map(fn ($id) => (int) $id);

            $unknown = $submitted->diff($expected);

            if ($unknown->isNotEmpty()) {
                $validator->errors()->add('attendances', 'Terdapat data siswa yang bukan anggota kelas ini.');

                return;
            }

            $missing = $expected->diff($submitted);

            if ($missing->isNotEmpty()) {
                $validator->errors()->add(
                    'attendances',
                    'Semua siswa kelas ini harus memiliki status absensi sebelum disimpan.'
                );
            }
        });
    }
}
