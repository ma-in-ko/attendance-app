@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')

<div class="auth">
    <h1 class="auth__title">会員登録</h1>

    <form action="{{ route('register') }}" method="POST">
        @csrf

        <div class="auth__form-group">
            <label class="auth__label" for="name">名前</label>
            <input
                class="auth__input"
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}">
            @error('name')
            <p>{{ $message }}</p>
            @enderror
        </div>

        <div class="auth__form-group">
            <label class="auth__label" for="email">メールアドレス</label>
            <input
                class="auth__input"
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}">
            @error('email')
            <p>{{ $message }}</p>
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
            <p>{{ $message }}</p>
            @enderror
        </div>

        <div class="auth__form-group">

            <label class="auth__label" for="password_confirmation">パスワード確認</label>

            <input
                class="auth__input"
                type="password"
                id="password_confirmation"
                name="password_confirmation">
            @error('password_confirmation')
            <p>{{ $message }}</p>
            @enderror
        </div>

        <button
            class="auth__button"
            type="submit">
            登録する
        </button>
    </form>

    <a
        href="{{ route('login') }}"
        class="auth__link">
        ログインはこちら
    </a>
</div>

@endsection