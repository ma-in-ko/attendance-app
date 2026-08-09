<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\AttendanceRecord;
use App\Http\Resources\AttendanceRecordResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexAttendanceRecordRequest;
use App\Http\Requests\Api\V1\StoreAttendanceRecordRequest;
use App\Http\Requests\Api\V1\UpdateAttendanceRecordRequest;
use Carbon\Carbon;

class AttendanceRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexAttendanceRecordRequest $request)
    {
        $perPage = $request->input('per_page', 20);
        $perPage = min($perPage, 100);

        $attendanceRecords = AttendanceRecord::with(
            'user',
            'breakTimes',
        )
            ->when($request->user_id, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($request->date, function ($query, $date) {
                $query->where('work_date', $date);
            })
            ->when($request->month, function ($query, $month) {
                $parts = explode('-', $month);
                $query->whereYear('work_date', $parts[0]);
                $query->whereMonth('work_date', $parts[1]);
            })
            ->latest('work_date')
            ->paginate($perPage);

        return AttendanceRecordResource::collection($attendanceRecords);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttendanceRecordRequest $request)
    {
        $validated = $request->validated();

        $clockIn = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $validated['date'] . ' ' . $validated['clock_in']
        );

        $clockOut = !empty($validated['clock_out'])
            ? Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $validated['date'] . ' ' . $validated['clock_out']
            )
            : null;

        $attendanceRecord = $request->user()
            ->attendanceRecords()
            ->create([
                'work_date' => $validated['date'],
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'note' => $validated['comment'] ?? null,
            ]);

        $attendanceRecord->load([
            'user',
            'breakTimes',
        ]);

        return (new AttendanceRecordResource($attendanceRecord))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(AttendanceRecord $attendanceRecord)
    {
        $attendanceRecord->load([
            'user',
            'breakTimes',
            'attendanceCorrectionRequests',
        ]);

        return new AttendanceRecordResource($attendanceRecord);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttendanceRecordRequest $request, AttendanceRecord $attendanceRecord)
    {
        $this->authorize('update', $attendanceRecord);

        $validated = $request->validated();

        $clockIn = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $validated['date'] . ' ' . $validated['clock_in']
        );

        $clockOut = !empty($validated['clock_out'])
            ? Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $validated['date'] . ' ' . $validated['clock_out']
            )
            : null;

        $attendanceRecord->update([
            'work_date' => $validated['date'],
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'note' => $validated['comment'] ?? null,
        ]);

        $attendanceRecord->load([
            'user',
            'breakTimes',
        ]);

        return new AttendanceRecordResource($attendanceRecord);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AttendanceRecord $attendanceRecord)
    {
        $this->authorize('delete', $attendanceRecord);

        $attendanceRecord->delete();

        return response()->noContent();
    }
}
