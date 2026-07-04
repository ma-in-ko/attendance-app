<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceCorrectionRequest;

class AttendanceCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->status ?? 'pending';

        $requests =
            AttendanceCorrectionRequest::with('attendanceRecord.user')
            ->whereHas('attendanceRecord', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->where(
                'is_approved',
                $status === 'approved'
            )
            ->get();

        return view(
            'attendance.correction.index',
            compact(
                'requests',
                'status'
            )
        );
    }
}
