<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'class_id',
        'student_number',
        'birth_date',
        'phone',
        'address',
    ];

    protected $casts = [
        // Format tanggal eksplisit agar konsisten di MySQL maupun SQLite.
        'birth_date' => 'date:Y-m-d',
    ];

    /* ---------------- Relasi ---------------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /* ---------------- Ringkasan kehadiran ---------------- */

    /**
     * Jumlah per status absensi dalam rentang tanggal (inklusif).
     *
     * @return array<string, int>
     */
    public function attendanceSummary(?CarbonInterface $from = null, ?CarbonInterface $to = null): array
    {
        $counts = $this->attendances()
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
            ->when($from, fn ($query) => $query->whereDate('attendance_sessions.session_date', '>=', $from->toDateString()))
            ->when($to, fn ($query) => $query->whereDate('attendance_sessions.session_date', '<=', $to->toDateString()))
            ->selectRaw('attendances.status as status, COUNT(*) as total')
            ->groupBy('attendances.status')
            ->pluck('total', 'status');

        $summary = [];

        foreach (array_keys(Attendance::STATUSES) as $status) {
            $summary[$status] = (int) ($counts[$status] ?? 0);
        }

        return $summary;
    }

    /**
     * Persentase kehadiran: hadir / total catatan absensi × 100.
     * Null jika belum ada catatan (UI menampilkan "Belum ada data").
     */
    public function attendancePercentage(?CarbonInterface $from = null, ?CarbonInterface $to = null): ?float
    {
        $summary = $this->attendanceSummary($from, $to);
        $total = array_sum($summary);

        if ($total === 0) {
            return null;
        }

        return round($summary[Attendance::STATUS_HADIR] / $total * 100, 1);
    }
}
