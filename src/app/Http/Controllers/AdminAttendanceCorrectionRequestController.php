<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\AttendanceCorrectionRequest;

class AdminAttendanceCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->tab ?? 'pendeng';

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
            AttendanceCorrectionRequest::findOrFail($id);

        return view('admin.attendance.correction.show', compact('attendanceCorrectionRequest')
        );
    }

    public function approve($id)
    {

        $attendanceCorrectionRequest =
            AttendanceCorrectionRequest::findOrFail($id);

        $attendanceRecord = $attendanceCorrectionRequest->attendanceRecord;

        $attendanceRecord->update([
            'clock_in' => $attendanceCorrectionRequest->requested_clock_in,
            'clock_out' => $attendanceCorrectionRequest->requested_clock_out,
            'note' => $attendanceCorrectionRequest->reason,
        ]);

        $attendanceCorrectionRequest->update([
            'is_approved' => true,
            'approved_at' => Carbon::now()
        ]);

        return redirect()->route('admin.request.index');

    }

}
