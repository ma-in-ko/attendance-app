@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')

<div class="auth">
    <h1 class="auth__title">ログイン</h1>

    <form action="{{ route('login') }}" method="POST" novalidate>
        @csrf


        <div class="auth__form-group">
            <label class="auth__label" for="email">メールアドレス</label>
            <input
                class="auth__input"
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}">
            @error('email')
            <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth__form-group">
            <label class="auth__label" for="password">パスワード</label>
            <input
                class="auth__input"
                type="password"
                id="password"
                name="password">
            @error('password')
            <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <button
            class="auth__button"
            type="submit">
            ログイン
        </button>

    </form>

    <a
        href="{{ route('register') }}"
        class="auth__link">
        会員登録はこちら
    </a>
</div>

@endsection