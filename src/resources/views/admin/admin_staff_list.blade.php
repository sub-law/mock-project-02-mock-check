@extends('layouts.app_admin')

@section('title', 'スタッフ一覧画面')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin_staff_list.css') }}">
@endsection

@section('content')
<div class="staff-list-wrapper">

    <div class="staff-list-header">
        <div class="staff-list-line"></div>
        <h1 class="staff-list-title">スタッフ一覧</h1>
    </div>

    <table class="staff-list-table">
        <thead>
            <tr>
                <th class="status-col">名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            {{-- 仮データ --}}
            <tr>
                <td>西 伶奈</td>
                <td>reina.n@coachtech.com</td>
                <td class="detail-cell">詳細</td>
            </tr>
            <tr>
                <td>山田 太郎</td>
                <td>taro.y@coachtech.com</td>
                <td class="detail-cell">詳細</td>
            </tr>
        </tbody>
    </table>

</div>
@endsection