<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\AttendanceCorrectionRequest;
use App\Models\BreakTime;

class AdminAttendanceCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->tab ?? 'pending';

        $requests = AttendanceCorrectionRequest::with('attendanceRecord.user')
        ->when($status === 'pending', function ($query) {
            $query->where('is_approved', false);
        })
        ->when($status === 'approved', function($query) {
            $query->where('is_approved', true);
        })
        ->get();

        return view(
            'admin.attendance.correction.index', compact('requests'));
    }

    public function show($id)
    {
        $attendanceCorrectionRequest =
            AttendanceCorrectionRequest::with(
                'attendanceCorrectionBreaks',
                'attendanceRecord.user'
            )->findOrFail($id);

        return view('admin.attendance.correction.show', compact('attendanceCorrectionRequest')
        );
    }

    public function approve($id)
    {

        $attendanceCorrectionRequest =
            AttendanceCorrectionRequest::with(
                'attendanceCorrectionBreaks'
            )->findOrFail($id);

        $attendanceRecord = $attendanceCorrectionRequest->attendanceRecord;

        $attendanceRecord->update([
            'clock_in' => $attendanceCorrectionRequest->requested_clock_in,
            'clock_out' => $attendanceCorrectionRequest->requested_clock_out,
            'note' => $attendanceCorrectionRequest->reason,
        ]);

        //既存休憩削除
        $attendanceRecord->breakTimes()->delete();

        //修正申請された休憩を登録
        foreach (
            $attendanceCorrectionRequest->attendanceCorrectionBreaks 
            as $break
        ) {
            BreakTime::create([
                'attendance_record_id'
                    => $attendanceRecord->id,

                'break_start'
                    =>$break->break_start,

                'break_end'
                    => $break->break_end,
            ]);
        }

        //承認済に変更
        $attendanceCorrectionRequest->update([
            'is_approved' => true,
            'approved_at' => Carbon::now()
        ]);

        return redirect()->route('request.index');

    }

}
