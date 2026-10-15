<?php

namespace App\Services;

use App\Models\Attendance;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AttendanceStats
{
    /**
     * Jumlah per status absensi, opsional dibatasi guru dan rentang tanggal.
     *
     * @return array<string, int>
     */
    public function statusCounts(?int $teacherId = null, ?CarbonInterface $from = null, ?CarbonInterface $to = null): array
    {
        $counts = Attendance::query()
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
            ->join('schedules', 'schedules.id', '=', 'attendance_sessions.schedule_id')
            ->when($teacherId, fn ($query) => $query->where('schedules.teacher_id', $teacherId))
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

    /** Persentase kehadiran dari jumlah per status. Null jika belum ada data. */
    public function percentage(array $counts): ?float
    {
        $total = array_sum($counts);

        if ($total === 0) {
            return null;
        }

        return round(($counts[Attendance::STATUS_HADIR] ?? 0) / $total * 100, 1);
    }

    /**
     * Seri tren harian untuk grafik.
     *
     * @return array{labels: array<int, string>, hadir: array<int, int>, total: array<int, int>, percentage: array<int, float|null>}
     */
    public function trendSeries(CarbonInterface $from, CarbonInterface $to, ?int $teacherId = null): array
    {
        /** @var Collection<string, object> $rows */
        $rows = Attendance::query()
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
            ->join('schedules', 'schedules.id', '=', 'attendance_sessions.schedule_id')
            ->when($teacherId, fn ($query) => $query->where('schedules.teacher_id', $teacherId))
            ->whereDate('attendance_sessions.session_date', '>=', $from->toDateString())
            ->whereDate('attendance_sessions.session_date', '<=', $to->toDateString())
            ->groupBy('attendance_sessions.session_date')
            ->selectRaw('attendance_sessions.session_date as session_date')
            ->selectRaw("SUM(attendances.status = 'hadir') as hadir")
            ->selectRaw('COUNT(*) as total')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->session_date)->toDateString());

        $series = [
            'labels' => [],
            'hadir' => [],
            'total' => [],
            'percentage' => [],
        ];

        for ($date = Carbon::parse($from)->startOfDay(); $date->lte($to); $date->addDay()) {
            $row = $rows->get($date->toDateString());

            $hadir = (int) ($row->hadir ?? 0);
            $total = (int) ($row->total ?? 0);

            $series['labels'][] = $date->translatedFormat('d M');
            $series['hadir'][] = $hadir;
            $series['total'][] = $total;
            $series['percentage'][] = $total > 0 ? round($hadir / $total * 100, 1) : null;
        }

        return $series;
    }
}
