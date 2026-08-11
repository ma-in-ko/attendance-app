@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/correction/index.css') }}">
@endsection

@section('content')
<div class="correction-list">
    <h1>申請一覧</h1>

    <div class="correction__tabs">

        <a class="correction__tab {{ $status === 'pending' ? 'active' : '' }}"
            href="{{ route('request.index', ['tab' => 'pending']) }}">
            承認待ち
        </a>

        <a class="correction__tab {{ $status === 'approved' ? 'active' : '' }}"
            href="{{ route('request.index', ['tab' => 'approved']) }}">
            承認済み
        </a>

    </div>

    <table>
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>

        <tbody>
            @foreach($requests as $correctionRequest)
            <tr>
                <td>{{ $correctionRequest->is_approved ? '承認済' : '承認待ち' }} </td>
                <td>{{ $correctionRequest->attendanceRecord->user->name }}</td>
                <td>{{ $correctionRequest->attendanceRecord->work_date->format('Y/m/d') }}</td>
                <td>{{ $correctionRequest->reason }}</td>
                <td>{{ $correctionRequest->created_at->format('Y/m/d H:i') }}</td>
                <td>
                    <a href="{{ route('admin.request.show', $correctionRequest) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection