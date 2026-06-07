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

    <form action="{{ route('attendance.clock-in') }}" method="POST">
        @csrf
        <button class="attendance__button">
            出勤
        </button>
    </form>

</div>
@endsection