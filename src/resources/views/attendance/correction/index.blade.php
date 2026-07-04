@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/correction/index.css') }}">
@endsection

@section('content')

<div class="correction">

    <h1>申請一覧</h1>


    <div class="correction__tabs">

        <a class="correction__tab {{ $status === 'pending' ? 'active' : '' }}"
        href="{{ route('request.index', ['status' => 'pending']) }}">
            承認待ち
        </a>

        <a class="correction__tab {{$status === 'approved' ? 'active' : '' }}"
        href="{{ route('request.index', ['status' => 'approved']) }}">
            承認済み
        </a>

    </div>


    <table class="correction__table">

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

            @foreach($requests as $request)

            <tr>

                <td>
                    {{ $request->is_approved ? '承認済み' : '承認待ち' }}
                </td>


                <td>
                    {{ $request->attendanceRecord->user->name }}
                </td>


                <td>
                    {{ Carbon\Carbon::parse($request->attendanceRecord->work_date)->format('Y/m/d') }}
                </td>


                <td>
                    {{ $request->reason }}
                </td>


                <td>
                    {{ $request->created_at->format('Y/m/d') }}
                </td>


                <td>
                    <a href="{{ route('attendance.show', $request->attendanceRecord) }}">
                        詳細
                    </a>
                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

</div>

@endsection