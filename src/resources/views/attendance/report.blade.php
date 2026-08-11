@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/report.css') }}">
@endsection

@section('content')
<div class="container">
    <h2>マイ勤怠レポート</h2>

    <p>総労働時間：{{ $totalWorkHours }}時間 {{ $totalWorkRemainMinutes }}分</p>
    <p>総残業時間：{{ $totalOvertimeHours }}時間 {{ $totalOvertimeRemainMinutes }}</p>
    <p>平均労働時間：{{ $averageWorkHours }}時間 {{ $averageWorkRemainMinutes }}分</p>
    <p>遅刻回数：{{ $lateCount }}回</p>
    <p>早退回数：{{ $earlyLeaveCount }}回</p>
    <p>長時間労働回数：{{ $longWorkCount }}回</p>
    @foreach ($monthlyReports as $report)
    <div class="report-month">
        <p>{{ $report['month']}}</p>

        <div class="graph-row">
            <span>勤務時間</span>

            <div class="graph">
                <div class="graph-work"
                    style="width: {{ $report['work'] * 2 }}px;">
                </div>
            </div>

            <span>{{ $report['work'] }} 時間</span>
        </div>

        <div class="graph-row">
            <span>残業</span>

            <div class="graph">
                <div class="graph-overtime"
                    style="width: {{$report['overtime'] * 8 }}px;">
                </div>
            </div>

            <span>{{ $report['overtime'] }}時間</span>
        </div>

    </div>

    @endforeach
</div>
@endsection