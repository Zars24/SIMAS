<?php

namespace App\Services;

use App\Models\Schedule;

class ScheduleConflictChecker
{
    /**
     * Periksa konflik jadwal guru dan kelas pada hari & jam yang bertabrakan.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, string> Pesan error per field (kosong jika tidak ada konflik).
     */
    public function conflicts(array $data, ?Schedule $ignore = null): array
    {
        $errors = [];

        $day = $data['day_of_week'] ?? null;
        $start = $data['start_time'] ?? null;
        $end = $data['end_time'] ?? null;
        $isActive = (bool) ($data['is_active'] ?? true);

        if (! $day || ! $start || ! $end || ! $isActive) {
            return $errors;
        }

        $overlap = fn ($query) => $query
            ->where('day_of_week', $day)
            ->where('is_active', true)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start);

        if (! empty($data['teacher_id'])) {
            $conflict = Schedule::query()
                ->where('teacher_id', $data['teacher_id'])
                ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
                ->where($overlap)
                ->with(['schoolClass', 'subject'])
                ->first();

            if ($conflict) {
                $errors['teacher_id'] = sprintf(
                    'Guru sudah mengajar %s di %s pada %s (%s).',
                    $conflict->subject->name,
                    $conflict->schoolClass->name,
                    $conflict->dayLabel(),
                    $conflict->timeRange()
                );
            }
        }

        if (! empty($data['class_id'])) {
            $conflict = Schedule::query()
                ->where('class_id', $data['class_id'])
                ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
                ->where($overlap)
                ->with(['subject'])
                ->first();

            if ($conflict) {
                $errors['class_id'] = sprintf(
                    'Kelas sudah memiliki jadwal %s pada %s (%s).',
                    $conflict->subject->name,
                    $conflict->dayLabel(),
                    $conflict->timeRange()
                );
            }
        }

        return $errors;
    }
}
