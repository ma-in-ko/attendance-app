<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\AttendanceRecord;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceCorrectionBreak;
use App\Models\BreakTime;
use App\Http\Requests\AttendanceRequest;

class AttendanceController extends Controller
{
    public function create()
    {
        $attendance = AttendanceRecord::where('user_id',auth()->id())
        ->whereDate('work_date', today())
        ->first();

        if (!$attendance) {
            $status = '勤務外';
            } elseif ($attendance->clock_out) {
                $status = '退勤済';
            } else {
                $latestBreak = $attendance->breakTimes()
                    ->latest('id')
                    ->first();

                if ($latestBreak && !$latestBreak->break_end) {
                    $status = '休憩中';

                } else {
                $status = '出勤中';
                }
            }

        return view('attendance.create', compact('status'));
    }

    public function clockIn()
    {
        $attendance = AttendanceRecord::where('user_id', auth()->id())
        ->whereDate('work_date', today())
        ->first();

        if ($attendance) {
            return redirect()->route('attendance.create');
        }

        AttendanceRecord::create([
            'user_id' => auth()->id(),
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        return redirect()->route('attendance.create');
    }

    public function clockOut()
    {
        $attendance = AttendanceRecord::where('user_id', auth()->id())
        ->whereDate('work_date', today())
        ->first();

        if(!$attendance || $attendance->clock_out) {
            return redirect()->route('attendance.create');
        }

        $attendance->update([
            'clock_out' => now(),
        ]);

        return redirect()->route('attendance.create');
    }

    public function breakStart()
    {
            $attendance = AttendanceRecord::where('user_id', auth()->id())
            ->whereDate('work_date', today())
            ->first();

            if (!$attendance) {
                return redirect()->route('attendance.create');
            }

            BreakTime::create ([
                'attendance_record_id' => $attendance->id,
                'break_start' => now(),
            ]);

            return redirect()->route('attendance.create');
    }

    public function breakEnd()
    {
        $attendance = AttendanceRecord::where('user_id', auth()->id())
        ->whereDate('work_date', today())
        ->first();

        $break = BreakTime::where('attendance_record_id', $attendance->id)
        ->whereNull('break_end')
        ->latest('id')
        ->first();

        $break->update([
            'break_end' => now(),
        ]);

        return redirect()->route('attendance.create');
    }

    public function index(Request $request)
    {
        $currentMonth = $request->month
            ?Carbon::parse($request->month)
            :now();

        $attendanceRecords = AttendanceRecord::with('breakTimes')->where('user_id', auth()->id())
        ->whereYear('work_date', $currentMonth->year)
        ->whereMonth('work_date', $currentMonth->month)
        ->orderBy('work_date')
        ->get()
        ->keyBy(function ($attendance) {
            return Carbon::parse($attendance->work_date)->format('Y-m-d');
        });

        $attendances = collect();

        $date =$currentMonth->copy()->startOfMonth();

        while ($date->month == $currentMonth->month) {

        $attendance = $attendanceRecords->get($date->format('Y-m-d'));

            $attendances->push([
                'date' => $date->copy(),
                'attendance' => $attendance,
            ]);

            $date->addDay();
        }

        return view('attendance.index', compact(
                'attendances',
                'currentMonth',
            ));
    }

    public function show(AttendanceRecord $attendance)
    {
        $pendingRequest = AttendanceCorrectionRequest::where(
            'attendance_record_id',
            $attendance->id
        )
        ->where('is_approved', false)
        ->exists();

        return view ('attendance.show', compact(
            'attendance',
            'pendingRequest'
        ));
    }

    public function store(
        AttendanceRecord $attendance,
        AttendanceRequest $request
    )
    {
        $date = $attendance->work_date->format('Y-m-d');

        $attendanceCorrectionRequest = AttendanceCorrectionRequest::create([
            'attendance_record_id' => $attendance->id,
            'requested_clock_in' => $date . ' ' . $request->requested_clock_in,
            'requested_clock_out' => $date . ' ' . $request->requested_clock_out,
            'reason' => $request->reason,
        ]);

        foreach ($request->breaks as $break)
            {
                if (
                    empty($break['break_start'])
                    && empty($break['break_end'])
                ){
                    continue;
                }

                AttendanceCorrectionBreak::create([
                    'attendance_correction_request_id'
                        => $attendanceCorrectionRequest->id,

                    'break_start'
                        => $date . ' ' . $break['break_start'],

                    'break_end'
                        =>$date . ' ' . $break['break_end'],

                ]);
            }

        return redirect()->back();
    }
}
