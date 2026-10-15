<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'session_date',
        'topic',
        'created_by',
        'submitted_at',
    ];

    protected $casts = [
        'session_date' => 'date',
        'submitted_at' => 'datetime',
    ];

    /* ---------------- Relasi ---------------- */

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ---------------- Helper ---------------- */

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    /**
     * Jumlah per status untuk sesi ini.
     *
     * @return array<string, int>
     */
    public function countsByStatus(): array
    {
        $counts = $this->relationLoaded('attendances')
            ? $this->attendances->countBy('status')
            : $this->attendances()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        $summary = [];

        foreach (array_keys(Attendance::STATUSES) as $status) {
            $summary[$status] = (int) ($counts[$status] ?? 0);
        }

        return $summary;
    }

    /** Apakah sesi masih boleh dikoreksi guru pemiliknya berdasarkan jendela edit. */
    public function isWithinEditWindow(?CarbonInterface $now = null): bool
    {
        $windowDays = (int) config('simas.attendance.edit_window_days', 7);

        if ($windowDays <= 0) {
            return false;
        }

        $now ??= now();

        return $this->session_date->diffInDays($now->startOfDay(), false) <= $windowDays
            && $this->session_date->lte($now->startOfDay()->addDay());
    }
}
