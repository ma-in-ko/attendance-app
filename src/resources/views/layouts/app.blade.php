<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('css')

    <title>勤怠アプリ</title>
</head>

<body>
    <header class="header">
        <div class="header__inner">
                <img src="{{ asset('images/logo.png') }}" alt="COACHTECH">

                @auth
                <nav class="header__navi">
                    <ul class="header__nav-list">
                        <li>
                            <a href="/attendance">勤怠</a>
                        </li>
                        <li>
                            <a href="/attendance/list">勤怠一覧</a>
                        </li>
                        <li>
                            <a href="/stamp_correction_request/list">申請</a>
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit">
                                    ログアウト
                                </button>
                            </form>
                        </li>
                    </ul>
                </nav>
            @endauth
        </div>

    </header>

    <main>
        @yield('content')
    </main>
</body>

</html>