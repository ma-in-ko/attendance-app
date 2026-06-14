@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/show.css') }}">
@endsection

@section('content')
<div class="attendance">
    <h1>勤怠詳細</h1>

    <table class="attendance__detail">

        <tbody>
            <tr>
                <th>名前</th>
                <td>
                    <div class="detail__content">
                        <span>{{ $attendance->user->name }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    <div class="detail__content">
                        <span class="detail__box">
                            {{ Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}
                        </span>
                        <span class="detail__box date">
                            {{ Carbon\Carbon::parse($attendance->work_date)->format('n月j日')}}
                        </span>
                    </div>
                </td>
            </tr>

            <tr>
                <th>出勤・退勤</th>
                <td>
                    <div class="detail__content">
                        <span class="detail__box time">
                            {{ Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}
                        </span>
                        <span class="detail__separator">
                            ～
                        </span>
                        <span class="detail__box time">
                            {{ Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}
                        </span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>休憩</th>
                <td>
                    <div class="detail__content">
                        <span class="detail__box time">
                            @if(isset($attendance->breakTimes[0]))
                            {{ Carbon\Carbon::parse($attendance->breakTimes[0]->break_start)->format('H:i') }}
                        </span>
                        <span class="detail__separator">
                            ～
                        </span>
                        <span class="detail__box time">
                            {{ Carbon\Carbon::parse($attendance->breakTimes[0]->break_end)->format('H:i') }}
                            @endif
                        </span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>休憩2</th>
                <td>
                    <div class="detail__content">
                        <span class="detail__box time">
                            @if(isset($attendance->breakTimes[1]))
                            {{ Carbon\Carbon::parse($attendance->breakTimes[1]->break_start)->format('H:i') }}
                            @endif
                        </span>
                        <span class="detail__separator">
                            ～
                        </span>
                        <span class="detail__box time">
                            @if(isset($attendance->breakTimes[1]))
                            {{ Carbon\Carbon::parse($attendance->breakTimes[1]->break_end)->format('H:i') }}
                            @endif
                        </span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td>
                    <div class="detail__content note">
                        {{ $attendance->note }}
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <form action="#" method="POST" class="attendance__button">
        <button type="submit">修正</button>
    </form>
</div>


@endsection