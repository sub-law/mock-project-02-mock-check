@extends('layouts.app')

@section('title', 'ログイン画面')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<main class="main-content">
    <div class="login-container">
        <h1 class="login-title">ログイン</h1>

        <form method="POST" action="{{ route('login') }}" class="login-form">
            @csrf

            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">パスワード</label>
                <input id="password" type="password" name="password" required>
                @error('password')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="login-button">ログインする</button>
        </form>

        <div class="login-link">
            <a href="{{ route('register') }}">会員登録はこちら</a>
        </div>
    </div>
</main>
@endsection