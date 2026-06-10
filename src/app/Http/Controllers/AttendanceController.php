<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRecord;
use App\Models\BreakTime;

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
                    ->latest()
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
        ->latest()
        ->first();

        $break->update([
            'break_end' => now(),
        ]);

        return redirect()->route('attendance.create');
    }
    
}

