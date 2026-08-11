<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminAttendanceRequest;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $currentDay = $request->day
            ? Carbon::parse($request->day)
            : now();

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

    public function update(
        AdminAttendanceRequest $request,
        AttendanceRecord $attendance
    ) {
        $date = $attendance->work_date->format('Y-m-d');

        $clockIn = Carbon::parse(
            $date.' '.$request->clock_in
        );

        $clockOut = Carbon::parse(
            $date.' '.$request->clock_out
        );

        $attendance->update([
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'note' => $request->note,
        ]);

        // 既存の休憩を削除
        $attendance->breakTimes()->delete();

        // 入力された休憩を登録
        foreach ($request->breaks ?? [] as $break) {

            if (
                empty($break['break_start']) &&
                empty($break['break_end'])
            ) {
                continue;
            }

            BreakTime::create([
                'attendance_record_id' => $attendance->id,

                'break_start' => $date.' '.$break['break_start'],

                'break_end' => $date.' '.$break['break_end'],
            ]);
        }

        return redirect()->route('admin.attendance.list')
            ->with('success', '勤怠情報を更新しました');
    }
}
