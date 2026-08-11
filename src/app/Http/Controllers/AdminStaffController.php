<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminStaffController extends Controller
{
    public function index()
    {
        $users = User::where('admin_status', false)->get();

        return view('admin.staff.index', compact('users'));
    }

    public function attendance(Request $request, User $user)
    {
        $currentMonth = $request->month
            ? Carbon::parse($request->month)
            : now();

        $attendanceRecords = AttendanceRecord::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereYear('work_date', $currentMonth->year)
            ->whereMonth('work_date', $currentMonth->month)
            ->orderBy('work_date')
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->work_date)->format('Y-m-d');
            });

        $attendances = collect();

        $date = $currentMonth->copy()->startOfMonth();

        while ($date->month == $currentMonth->month) {
            $attendance = $attendanceRecords->get($date->format('Y-m-d'));

            $attendances->push([
                'date' => $date->copy(),
                'attendance' => $attendance,
            ]);

            $date->addDay();
        }

        return view('admin.staff.attendance', compact(
            'user',
            'attendances',
            'currentMonth',
        ));
    }

    public function export(Request $request, User $user)
    {
        $currentMonth = $request->month
            ? Carbon::parse($request->month)
            : now();

        $attendanceRecords = AttendanceRecord::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereYear('work_date', $currentMonth->year)
            ->whereMonth('work_date', $currentMonth->month)
            ->orderBy('work_date')
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->work_date)->format('Y-m-d');
            });

        $attendances = collect();

        $date = $currentMonth->copy()->startOfMonth();

        while ($date->month == $currentMonth->month) {

            $attendance = $attendanceRecords->get($date->format('Y-m-d'));

            $attendances->push([
                'date' => $date->copy(),
                'attendance' => $attendance,
            ]);

            $date->addDay();

        }

        return response()->streamDownload(function () use ($attendances) {

            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                '日付',
                '出勤',
                '退勤',
                '休憩',
                '合計',
            ]);

            foreach ($attendances as $item) {

                $attendance = $item['attendance'];
                $date = $item['date'];

                $breakMinutes = 0;

                if ($attendance) {
                    foreach ($attendance->breakTimes as $breakTime) {
                        if ($breakTime->break_start && $breakTime->break_end) {
                            $breakMinutes += Carbon::parse($breakTime->break_start)
                                ->diffInMinutes(Carbon::parse($breakTime->break_end));
                        }
                    }
                }

                $breakTimeFormatted = '';

                if ($attendance) {
                    $breakTimeFormatted = sprintf(
                        '%02d:%02d',
                        floor($breakMinutes / 60),
                        $breakMinutes % 60
                    );
                }

                $workTimeFormatted = '';

                if ($attendance && $attendance->clock_in && $attendance->clock_out) {

                    $workMinutes = Carbon::parse($attendance->clock_in)
                        ->diffInMinutes(Carbon::parse($attendance->clock_out));

                    $workMinutes -= $breakMinutes;

                    $workTimeFormatted = sprintf(
                        '%02d:%02d',
                        floor($workMinutes / 60),
                        $workMinutes % 60
                    );
                }

                fputcsv($handle, [
                    $date->format('Y/m/d'),
                    $attendance && $attendance->clock_in
                        ? Carbon::parse($attendance->clock_in)->format('H:i')
                        : '',
                    $attendance && $attendance->clock_out
                        ? Carbon::parse($attendance->clock_out)->format('H:i')
                        : '',
                    $breakTimeFormatted,
                    $workTimeFormatted,
                ]);
            }

            fclose($handle);
        }, $user->name.'_'.$currentMonth->format('Y_m').'_attendance.csv');
    }
}
