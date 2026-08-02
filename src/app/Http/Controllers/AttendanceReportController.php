<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceReportController extends Controller
{
    public function index()
    {
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();

        $attendanceRecords = AttendanceRecord::with('breakTimes')
            ->where('user_id', auth()->id())
            ->whereDate('work_date', '>=', $sixMonthsAgo)
            ->orderBy('work_date')
            ->get();

        $totalWorkMinutes = 0;
        $totalOvertimeMinutes = 0;

        foreach ($attendanceRecords as $attendance) {

            $actualWorkMinutes = $attendance->getActualWorkMinutes();

            $totalWorkMinutes += $actualWorkMinutes;

            if ($actualWorkMinutes > 480) {
                $totalOvertimeMinutes += ($actualWorkMinutes - 480);
            }
        }

        $currentMonthRecords = $attendanceRecords->filter(function ($attendance) {
            return $attendance->work_date->isCurrentMonth();
        });

        $lateCount = $currentMonthRecords->filter(function ($attendance) {
            if (!$attendance->clock_in) {
                return false;
            }

            $clockIn = Carbon::parse($attendance->clock_in);

            return $clockIn->format('H:i') > '09:00';
        })->count();

        $earlyLeaveCount = $currentMonthRecords->filter(function ($attendance) {
            if (!$attendance->clock_out) {
                return false;
            }

            $clockOut = Carbon::parse($attendance->clock_out);

            return $clockOut->format('H:i') < '18:00';
        })->count();

        $longWorkCount = $currentMonthRecords->filter(function ($attendance) {

        return $attendance->getActualWorkMinutes() >= 720;

        })->count();

        $totalWorkHours = floor($totalWorkMinutes / 60);
        $totalWorkRemainMinutes = $totalWorkMinutes % 60;

        $totalOvertimeHours = floor($totalOvertimeMinutes / 60);
        $totalOvertimeRemainMinutes = $totalOvertimeMinutes % 60;

        $averageWorkMinutes = $attendanceRecords->count() > 0
            ? floor($totalWorkMinutes / $attendanceRecords->count())
            : 0;

        $averageWorkHours = floor($averageWorkMinutes / 60);
        $averageWorkRemainMinutes = $averageWorkMinutes % 60;

        $monthlyReports = [];

        for ($i = 5; $i >= 0; $i--) {

            $month = Carbon::now()->subMonths($i);

            $monthRecords = $attendanceRecords->filter(function ($attendance) use ($month) {
                return $attendance->work_date->year == $month->year
                    && $attendance->work_date->month == $month->month;
            });

            $monthWorkMinutes = 0;
            $monthOvertimeMinutes = 0;

            foreach ( $monthRecords as $attendance) {

                $actualWorkMinutes = $attendance->getActualWorkMinutes();

                $monthWorkMinutes += $actualWorkMinutes;

                if ($actualWorkMinutes > 480) {
                    $monthOvertimeMinutes += ($actualWorkMinutes - 480);
                }
            }

            $monthlyReports[] = [
                'month' => $month->format('Y年n月'),
                'work' => round($monthWorkMinutes / 60, 1),
                'overtime' => round($monthOvertimeMinutes / 60, 1),
            ];
        }

        return view('attendance.report', compact(
            'attendanceRecords',
            'totalWorkHours',
            'totalWorkRemainMinutes',
            'totalOvertimeHours',
            'totalOvertimeRemainMinutes',
            'averageWorkHours',
            'averageWorkRemainMinutes',
            'lateCount',
            'earlyLeaveCount',
            'longWorkCount',
            'monthlyReports',
        ));
    }

    
}
