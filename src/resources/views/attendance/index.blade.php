@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')

<div class="attendance">
    <h1>勤怠一覧</h1>

    <div class="month-nav">
        <a href="{{ route('attendance.index', [
            'month' => $currentMonth->copy()->subMonth()->format('Y-m') ]) }}">
            ← 前月
        </a>

        <span class="month-nav__current">
            📅{{ $currentMonth->format('Y/m') }}
        </span>

        <a href="{{ route('attendance.index', [
            'month' => $currentMonth->copy()->addMonth()->format('Y-m') ]) }}">
            翌月 →
        </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>

        <tbody>
            @foreach($attendances as $item)

            @php
            $attendance = $item['attendance'];
            $date = $item['date'];
            @endphp

            @php
            $breakMinutes = 0;

            $breakTimes = $attendance ? $attendance->breakTimes : collect();

            foreach ($breakTimes as $breakTime) {
                if ($breakTime->break_start && $breakTime->break_end) {
                    $breakMinutes += \Carbon\Carbon::parse      ($breakTime->break_start)
                        ->diffInMinutes(
                            \Carbon\Carbon::parse($breakTime->break_end)
                    );
                }
            }
            @endphp

            @php
            $breakHours = floor($breakMinutes / 60);
            $breakRemainMinutes = $breakMinutes % 60;

            $breakTimeFormatted = $attendance
            ? sprintf(
            '%02d:%02d',
            $breakHours,
            $breakRemainMinutes
            ) : '';
            @endphp

            @php
            $workMinutes = 0;

            if ($attendance && $attendance->clock_in && $attendance->clock_out) {
            $workMinutes =
            \Carbon\Carbon::parse($attendance->clock_in)
            ->diffInMinutes(
            \Carbon\Carbon::parse($attendance->clock_out)
            );

            $workMinutes -= $breakMinutes;
            }

            $workHours = floor($workMinutes / 60);
            $workRemainMinutes = $workMinutes % 60;

            $workTimeFormatted = $attendance
            ? sprintf(
            '%02d:%02d',
            $workHours,
            $workRemainMinutes
            ) : '';
            @endphp

            <tr>
                <td>{{ $date->format('m/d') }}
                    ({{ ['日','月','火','水','木','金','土'][$date->dayOfWeek] }})
                </td>
                <td>{{ $attendance && $attendance->clock_in
                        ?\Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
                        : ''
                    }}</td>
                <td>{{ $attendance && $attendance->clock_out
                        ?\Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                        : ''
                    }}</td>
                <td>{{ $breakTimeFormatted }}</td>
                <td>{{ $workTimeFormatted }}</td>
                <td>
                    @if($attendance)
                        <a href="{{ route('attendance.show', $attendance) }}">
                            詳細
                        </a>
                    @else
                        <span class="detail-text"> 詳細</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>


@endsection