@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/create.css') }}">
@endsection

@section('content')
<div class="attendance">
    <div class="attendance__status">
        {{ $status }}
    </div>
    <div class="attendance__date">
        @php
        $weekdays = ['日','月','火','水','木','金','土'];
        @endphp
        {{ now()->format('Y年n月j日') }}({{ $weekdays[now()->dayOfWeek] }})
    </div>

    <div class="attendance__time">
        {{ now()->format('H:i') }}
    </div>

    <div class="attendance__actions">
        @if ($status === '勤務外')
        <form action="{{ route('attendance.clock-in') }}" method="POST">
            @csrf
            <button type="submit" class="attendance__button">
                出勤
            </button>
        </form>
        @endif

        @if($status === '出勤中')
        <form action="{{ route('attendance.clock-out') }}" method="POST">
            @csrf
            <button type="submit" class="attendance__button">退勤</button>
        </form>

        <form action="{{ route('attendance.break-start') }}" method="POST">
            @csrf
            <button type="submit" class="attendance__button start-break">休憩入</button>
        </form>
        @endif
        @if ($status === '休憩中')

        <form action="{{ route('attendance.break-end') }}" method="post">
            @csrf
            <button type="submit" class="attendance__button end-break"> 休憩戻</button>
        </form>
        @endif
    </div>

    @if ($status === '退勤済')
    <p class="clock-out_message">お疲れ様でした。</p>
    @endif

</div>
@endsection