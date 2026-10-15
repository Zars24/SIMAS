<?php

namespace App\Policies;

use App\Models\AttendanceSession;
use App\Models\User;

class AttendanceSessionPolicy
{
    /** Admin melihat semua; guru hanya sesi jadwal yang ditugaskan. */
    public function view(User $user, AttendanceSession $session): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isGuru() && $this->ownsSchedule($user, $session);
    }

    /** Membuat/mengisi sesi: hanya guru pemilik jadwal (route guru sudah dibatasi role). */
    public function create(User $user): bool
    {
        return $user->isGuru() || $user->isAdmin();
    }

    /**
     * Koreksi: guru pemilik jadwal dan masih dalam jendela edit;
     * admin boleh mengoreksi (tercatat di audit log).
     */
    public function update(User $user, AttendanceSession $session): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isGuru()
            && $this->ownsSchedule($user, $session)
            && $session->isWithinEditWindow();
    }

    public function delete(User $user, AttendanceSession $session): bool
    {
        return false;
    }

    private function ownsSchedule(User $user, AttendanceSession $session): bool
    {
        return $user->teacher !== null
            && $session->schedule !== null
            && $session->schedule->teacher_id === $user->teacher->id;
    }
}
