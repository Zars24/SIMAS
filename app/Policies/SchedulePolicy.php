<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class SchedulePolicy
{
    /** Guru hanya boleh melihat/mengisi jadwal yang menjadi tanggung jawabnya. */
    public function view(User $user, Schedule $schedule): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->owns($user, $schedule);
    }

    public function fill(User $user, Schedule $schedule): bool
    {
        return $this->owns($user, $schedule);
    }

    private function owns(User $user, Schedule $schedule): bool
    {
        return $user->isGuru()
            && $user->teacher !== null
            && $schedule->teacher_id === $user->teacher->id;
    }
}
