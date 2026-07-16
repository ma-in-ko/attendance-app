<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AttendanceRecord;

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
            ?Carbon::parse($request->month)
            :now();

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
}
