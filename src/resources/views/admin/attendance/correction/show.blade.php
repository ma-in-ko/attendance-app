@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/correction/show.css') }}">
@endsection

@section('content')
<div class="correction-detail">
    <h1>勤怠詳細</h1>

    <form action="{{ route('admin.request.approve', $attendanceCorrectionRequest->id) }}" method="POST">
        @csrf

        <table class="correction-detail__table">
            <tbody>

                <tr>
                    <th>名前</th>
                    <td>
                        <div class="correction-detail__content">
                            <span class="correction-detail__box">
                                {{ $attendanceCorrectionRequest->attendanceRecord->user->name }}
                            </span>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>日付</th>
                    <td>
                        <div class="correction-detail__content">
                            <span class="correction-detail__box">
                                {{ Carbon\Carbon::parse($attendanceCorrectionRequest->attendanceRecord->work_date)->format('Y年') }}
                            </span>
                            <span class="correction-detail__separator"></span>
                            <span class="correction-detail__box">
                                {{ Carbon\Carbon::parse($attendanceCorrectionRequest->attendanceRecord->work_date)->format('n月j日') }}
                            </span>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="correction-detail__content">
                            <span class="correction-detail__time">
                                {{ Carbon\Carbon::parse($attendanceCorrectionRequest->requested_clock_in)->format('H:i') }}
                            </span>

                            <span class="correction-detail__separator">～</span>

                            <span class="correction-detail__time">
                                {{ Carbon\Carbon::parse($attendanceCorrectionRequest->requested_clock_out)->format('H:i') }}
                            </span>
                        </div>
                    </td>
                </tr>

                @foreach($attendanceCorrectionRequest->attendanceCorrectionBreaks as $index => $break)

                <tr>
                    <th>
                        休憩{{ $index === 0 ? '' : $index + 1 }}
                    </th>
                    <td>
                        <div class="correction-detail__content">
                            <span class="correction-detail__time">
                                {{ Carbon\Carbon::parse($break->break_start)->format('H:i') }}
                            </span>

                            <span class="correction-detail__separator">
                                ～
                            </span>

                            <span class="correction-detail__time">
                                {{ Carbon\Carbon::parse($break->break_end)->format('H:i') }}
                            </span>
                        </div>
                    </td>
                </tr>

                @endforeach

                <tr>

                <tr>
                    <th>備考</th>
                    <td>
                        <span
                            class="correction-detail__note"
                            name="reason">{{ $attendanceCorrectionRequest->reason }}</span>
                    </td>
                </tr>

            </tbody>
        </table>

        <div class="correction-detail__button">
            @if($attendanceCorrectionRequest->is_approved)

            <button class="approved-button" disabled>
                承認済み
            </button>

            @else

            <button type="submit">
                承認
            </button>

            @endif
        </div>

    </form>
</div>
@endsection
