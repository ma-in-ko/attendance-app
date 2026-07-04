<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceCorrectionRequest;

class AdminAttendanceCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = AttendanceCorrectionRequest::with('attendanceRecord.user')
        ->get();

        return view(
            'admin.attendance.correction.index', compact('requests'));
    }

    public function show(AttendanceCorrectionRequest $attendanceCorrectionRequest)
    {

        return view('admin.attendance.correction.show', compact('attendanceCorrectionRequest')
        );
    }

}
