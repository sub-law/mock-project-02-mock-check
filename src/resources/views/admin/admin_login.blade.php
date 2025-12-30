@extends('layouts.guest')

@section('title', 'ログイン画面（管理者）')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<main class="main-content">
    <div class="login-container">
        <h1 class="login-title">管理者ログイン</h1>

        @if (session('status'))
        <div class="flash-message">
            {{ session('status') }}
        </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}" class="login-form">
            @csrf

            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input id="email" type="text" name="email" value="{{ old('email') }}">
                @error('email')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">パスワード</label>
                <input id="password" type="password" name="password">
                @error('password')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="login-button">管理者ログインする</button>
        </form>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const flash = document.querySelector('.flash-message');
        if (flash) {
            setTimeout(() => {
                flash.style.opacity = '0';
                flash.style.transition = 'opacity 0.5s ease';
            }, 3000);
        }
    });
</script>

@endsection