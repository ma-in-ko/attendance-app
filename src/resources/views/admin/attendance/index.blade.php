@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/index.css') }}">
@endsection

@section('content')

<div class="attendance">
    <h1>{{ $currentDay -> format('Y年n月j日') }}の勤怠</h1>

    <div class="date-nav">
        <a href="{{ route('admin.attendance.list', ['day' => $currentDay->copy()->subDay()->format('Y-m-d')]) }}">
            ←前日
        </a>
        <span class="nav__current">
            📅{{ $currentDay -> format('Y/m/d') }}
        </span>

        <a href="{{ route('admin.attendance.list', ['day' => $currentDay->copy()->addDay()->format('Y-m-d')]) }}">
            翌日→
        </a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>

        <tbody>
            @foreach($attendances as $attendance)

            @php
            $breakMinutes = 0;

            foreach ($attendance->breakTimes as $breakTime) {
            if ($breakTime->break_start && $breakTime->break_end) {
            $breakMinutes += \Carbon\Carbon::parse($breakTime->break_start)
            ->diffInMinutes(
            \Carbon\Carbon::parse($breakTime->break_end)
            );
            }
            }
            @endphp

            @php
            $breakHours = floor($breakMinutes / 60);
            $breakRemainMinutes = $breakMinutes % 60;

            $breakTimeFormatted = sprintf(
            '%02d:%02d',
            $breakHours,
            $breakRemainMinutes
            );
            @endphp

            @php
            $workMinutes = 0;

            if ($attendance->clock_in && $attendance->clock_out) {
            $workMinutes =
            \Carbon\Carbon::parse($attendance->clock_in)
            ->diffInMinutes(
            \Carbon\Carbon::parse($attendance->clock_out)
            );

            $workMinutes -= $breakMinutes;
            }

            $workHours = floor($workMinutes / 60);
            $workRemainMinutes = $workMinutes % 60;

            $workTimeFormatted = sprintf(
            '%02d:%02d',
            $workHours,
            $workRemainMinutes
            );
            @endphp

            <tr>
                <td>
                        {{$attendance->user->name }}
                </td>
                <td>{{ $attendance->clock_in
                        ?\Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
                        : ''
                    }}</td>
                <td>{{ $attendance->clock_out
                        ?\Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                        : ''
                    }}</td>
                <td>{{ $breakTimeFormatted }}</td>
                <td>{{ $workTimeFormatted }}</td>
                <td>
                    <a href="{{ route('admin.attendance.detail', $attendance) }}" class="detail-text">
                        詳細
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>


@endsection