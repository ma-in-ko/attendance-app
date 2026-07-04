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

        $attendances = AttendanceRecord::with('breakTimes')->where('user_id', $user->id)
        ->whereYear('work_date', $currentMonth->year)
        ->whereMonth('work_date', $currentMonth->month)
        ->orderBy('work_date')
        ->get();

        return view('admin.staff.attendance', compact(
                'user',
                'attendances',
                'currentMonth',
            ));
    }
}
