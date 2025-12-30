@extends('layouts.app')

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
            @forelse ($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>

                <td class="detail-cell">
                    <a href="{{ route('admin.staff.attendance', ['id' => $user->id]) }}">
                        詳細
                    </a>
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="3" class="no-data">スタッフが登録されていません</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection