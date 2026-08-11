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
                        <div class="detail__content name">
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
                            <span class="detail__box year">
                                {{ $attendance->work_date->format('Y年') }}
                            </span>
                            <span></span>
                            <span class="detail__box date">
                                {{ $attendance->work_date->format('n月j日') }}
                            </span>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="time__content">

                            @if($pendingRequest)

                            <span class="detail__box time">
                                {{ Carbon\Carbon::parse($pendingRequest->requested_clock_in)->format('H:i') }}
                            </span>

                            <span class="detail__separator">～</span>

                            <span class="detail__box time">
                                {{ $pendingRequest->requested_clock_out ? Carbon\Carbon::parse($pendingRequest->requested_clock_out)->format('H:i') : '' }}
                            </span>

                            @else

                            <input class="detail__box time"
                                type="time"
                                name="requested_clock_in"
                                value="{{ old('requested_clock_in', Carbon\Carbon::parse($attendance->clock_in)->format('H:i')) }}">

                            <span class="detail__separator">
                                ～
                            </span>

                            <input class="detail__box time"
                                type="time"
                                name="requested_clock_out"
                                value="{{old('requested_clock_out', $attendance->clock_out
                                    ? Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                                : '') }}">

                            @endif

                        </div>

                        @if ($errors->has('requested_clock_in') || $errors->has('requested_clock_out'))
                        <p class="error">出勤時間もしくは退勤時間が不適切な値です</p>
                        @endif
                    </td>
                </tr>

                @php
                $breakTimes = $pendingRequest
                ? $pendingRequest->attendanceCorrectionBreaks
                : $attendance->breakTimes;
                @endphp

                @foreach($breakTimes as $index => $breakTime)

                <tr>
                    <th>
                        休憩{{ $index === 0 ? '' : $index + 1 }}
                    </th>

                    <td>
                        <div class="time__content">

                            @if($pendingRequest)

                            <span class="detail__box time">
                                {{ $breakTime->break_start ? Carbon\Carbon::parse($breakTime->break_start)->format('H:i') : '' }}
                            </span>

                            <span class="detail__separator">～</span>

                            <span class="detail__box time">
                                {{ $breakTime->break_end ? Carbon\Carbon::parse($breakTime->break_end)->format('H:i') : '' }}
                            </span>

                            @else

                            <input class="detail__box time"
                                type="time"
                                name="breaks[{{ $index }}][break_start]"
                                value="{{ old("breaks.$index.break_start", $breakTime->break_start ? Carbon\Carbon::parse($breakTime->break_start)->format('H:i') : '') }}">

                            <span class="detail__separator">
                                ～
                            </span>

                            <input class="detail__box time"
                                type="time"
                                name="breaks[{{ $index }}][break_end]"
                                value="{{ old("breaks.$index.break_end", $breakTime->break_end
                                    ? Carbon\Carbon::parse($breakTime->break_end)->format('H:i')
                                    : '') }}">

                            @endif

                        </div>


                        @if (
                        $errors->has("breaks.$index.break_start") ||
                        $errors->has("breaks.$index.break_end")
                        )
                        <p class="error">
                            {{ $errors->first("breaks.$index.break_start") ?: $errors->first("breaks.$index.break_end") }}
                        </p>
                        @endif
                    </td>
                </tr>

                @endforeach


                @php
                $newIndex = $attendance->breakTimes->count();
                @endphp

                @if(!$pendingRequest)

                <tr>
                    <th>
                        休憩{{ $newIndex + 1 }}
                    </th>

                    <td>
                        <div class="time__content">

                            <input
                                class="detail__box time"
                                type="time"
                                name="breaks[{{ $newIndex }}][break_start]"
                                value="{{ old("breaks.$newIndex.break_start") }}">

                            <span class="detail__separator">
                                ～
                            </span>

                            <input
                                class="detail__box time"
                                type="time"
                                name="breaks[{{ $newIndex }}][break_end]"
                                value="{{ old("breaks.$newIndex.break_end") }}">

                        </div>

                        @if (
                        $errors->has("breaks.$newIndex.break_start") ||
                        $errors->has("breaks.$newIndex.break_end")
                        )
                        <p class="error">
                            {{ $errors->first("breaks.$newIndex.break_start") ?: $errors->first("breaks.$newIndex.break_end") }}
                        </p>
                        @endif
                    </td>
                </tr>
                @endif


                <tr>
                    <th>備考</th>
                    <td>

                        @if($pendingRequest)

                        <span class="note-text">
                            {{ $pendingRequest->reason }}
                        </span>

                        @else

                        <textarea
                            class="note"
                            name="reason">{{ old('reason', $attendance->note) }}</textarea>
                        @endif

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