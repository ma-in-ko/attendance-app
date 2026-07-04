@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/detail.css') }}">
@endsection

@section('content')
<div class="admin-attendance">
    <h1>勤怠詳細</h1>

    <form action="{{ route('admin.attendance.update', $attendance) }}" method="POST">
        @csrf
        @method('PUT')

        <table class="admin-attendance__detail">

            <tbody>
                <tr>
                    <th>名前</th>
                    <td>
                        <div class="admin-detail__content">
                            <span>
                                {{ $attendance->user->name }}
                            </span>
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
                            <input class="detail__box time"
                                type="time"
                                name="clock_in"
                                value="{{ Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}"
                                {{ $pendingRequest ? 'disabled' : '' }}>
                            <span class="detail__separator">
                                ～
                            </span>
                            <input class="detail__box time"
                                type="time"
                                name="clock_out"
                                value="{{$attendance->clock_out
                                ? Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                                : '' }}"
                                {{ $pendingRequest ? 'disabled' : '' }}>
                        </div>
                        @if ($errors->has('clock_in') || $errors->has('clock_out'))
                        <p class="error">出勤時間もしくは退勤時間が不適切な値です</p>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>休憩</th>
                    <td>
                        <div class="detail__content">
                            <input class="detail__box time"
                                type="time"
                                name="breaks[0][break_start]"
                                value="{{ isset($attendance->breakTimes[0])
                                ?Carbon\Carbon::parse($attendance->breakTimes[0]->break_start)->format('H:i')
                                : '' }}"
                                {{ $pendingRequest ? 'disabled' : '' }}>
                            <span class="detail__separator">
                                ～
                            </span>
                            <input class="detail__box time"
                                type="time"
                                name="breaks[0][break_end]"
                                value="{{ isset($attendance->breakTimes[0])
                                && $attendance->breakTimes[0]->break_end
                                ? Carbon\Carbon::parse($attendance->breakTimes[0]->break_end)->format('H:i')
                                : '' }}"
                                {{ $pendingRequest ? 'disabled' : '' }}>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>休憩2</th>
                    <td>
                        <div class="detail__content">
                            <input class="detail__box time"
                                type="time"
                                name="breaks[1][break_start]"
                                value="{{ isset($attendance->breakTimes[1])
                                ? Carbon\Carbon::parse($attendance->breakTimes[1]->break_start)->format('H:i')
                                : '' }}"
                                {{ $pendingRequest ? 'disabled' : '' }}>
                            <span class="detail__separator">
                                ～
                            </span>
                            <input class="detail__box time"
                                type="time"
                                name="breaks[1][break_end]"
                                value="{{ isset($attendance->breakTimes[1])
                                && $attendance->breakTimes[1]->break_end
                                ? Carbon\Carbon::parse($attendance->breakTimes[1]->break_end)->format('H:i')
                                : '' }}"
                                {{ $pendingRequest ? 'disabled' : '' }}>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td>
                        <textarea
                            class="detail__content note"
                            name="note"
                            {{ $pendingRequest ? 'disabled' : '' }}>{{ $attendance->note }}</textarea>
                        @error('note')
                        <p class="error">{{$message }}</p>
                        @enderror
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="attendance__button">
            @if($pendingRequest)
            <p class="pending">*承認待ちのため修正はできません</p>
            @else
            <button type="submit">修正</button>
            @endif
        </div>
    </form>
</div>


@endsection