@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/show.css') }}">
@endsection

@section('content')
<div class="attendance">
    <h1>勤怠詳細</h1>

    <form action="{{ route('attendance.request', $attendance) }}" method="POST">
        @csrf

        <table class="attendance__detail">

            <tbody>
                <tr>
                    <th>名前</th>
                    <td>
                        <div class="detail__content">
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
                                name="requested_clock_in"
                                value="{{ Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}"
                                {{ $pendingRequest ? 'disabled' : '' }}>
                            <span class="detail__separator">
                                ～
                            </span>
                            <input class="detail__box time"
                                type="time"
                                name="requested_clock_out"
                                value="{{$attendance->clock_out
                                ? Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                                : '' }}"
                                {{ $pendingRequest ? 'disabled' : '' }}>
                        </div>
                        @if ($errors->has('requested_clock_in') || $errors->has('requested_clock_out'))
                        <p class="error">出勤時間もしくは退勤時間が不適切な値です</p>
                        @endif
                    </td>
                </tr>

                @foreach($attendance->breakTimes as $index => $breakTime)

                <tr>
                    <th>
                        休憩{{ $index === 0 ? '' : $index + 1 }}
                    </th>

                    <td>
                        <div class="detail__content">

                            <input class="detail__box time"
                                type="time"
                                name="breaks[{{ $index }}][break_start]"
                                value="{{ Carbon\Carbon::parse($breakTime->break_start)->format('H:i') }}"
                                {{ $pendingRequest ? 'disabled' : '' }}>

                            <span class="detail__separator">
                                ～
                            </span>

                            <input class="detail__box time"
                                type="time"
                                name="breaks[{{ $index }}][break_end]"
                                value="{{ $breakTime->break_end
                    ? Carbon\Carbon::parse($breakTime->break_end)->format('H:i')
                    : '' }}"
                                {{ $pendingRequest ? 'disabled' : '' }}>

                        </div>
                    </td>
                </tr>

                @endforeach

                <tr>
                    <th>
                        休憩{{ $attendance->breakTimes->count() + 1 }}
                    </th>

                    <td>
                        <div class="detail__content">

                            <input class="detail__box time"
                                type="time"
                                name="breaks[{{ $attendance->breakTimes->count() }}][break_start]"
                                {{ $pendingRequest ? 'disabled' : '' }}>

                            <span class="detail__separator">
                                ～
                            </span>

                            <input class="detail__box time"
                                type="time"
                                name="breaks[{{ $attendance->breakTimes->count() }}][break_end]"
                                {{ $pendingRequest ? 'disabled' : '' }}>

                        </div>
                    </td>
                </tr>


                <tr>
                    <th>備考</th>
                    <td>
                        <textarea
                            class="detail__content note"
                            name="reason"
                            {{ $pendingRequest ? 'disabled' : '' }}>{{ $attendance->note }}</textarea>
                        @error('reason')
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