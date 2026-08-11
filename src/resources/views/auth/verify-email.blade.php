@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection

@section('content')

    <div class="verify">
        <p class="message">
            登録していただたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <a href="http://localhost:8025" target="_blank" class="verify-button">
        認証はこちらから
        </a>

        <form action="{{ route('verification.send') }}" method="post">
            @csrf

            <button class="resend-button" type="submit">
                認証メールを再送する
            </button>
        </form>
    </div>
@endsection