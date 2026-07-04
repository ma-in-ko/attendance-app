<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\AttendanceRecord;
use App\Models\AttendanceCorrectionRequest;


class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $currentDay = $request->day
            ?Carbon::parse($request->day)
            :now();

        $attendances = AttendanceRecord::with('user', 'breakTimes')->whereDate('work_date', $currentDay)
        ->get();


        return view('admin.attendance.index', compact(
            'currentDay',
            'attendances',
        ));
    }

    public function detail(AttendanceRecord $attendance)
    {

        $pendingRequest = AttendanceCorrectionRequest::where(
            'attendance_record_id',
            $attendance->id
        )
            ->where('is_approved', false)
            ->exists();

        return view('admin.attendance.detail', compact(
            'attendance',
            'pendingRequest'
        ));
    }

    public function update(Request $request, AttendanceRecord $attendance)
    {
        $clockIn = Carbon::parse(
            $attendance->work_date . ' ' . $request->clock_in
        );

        $clockOut = Carbon::parse(
            $attendance->work_date . ' ' . $request->clock_out
        );

        $attendance->update([
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'note' => $request->note,
        ]);

        return redirect()->route('admin.attendance.list')
        ->with('success', '勤怠情報を更新しました');
    }
}
