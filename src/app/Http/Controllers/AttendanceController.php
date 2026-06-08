<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRecord;

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
                $status = '出勤中';
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
}

