@extends('layouts.app')

@section('title', '申請一覧画面')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request.css') }}">
@endsection

@section('content')
<div class="request-list-wrapper">

    <div class="request-header">
        <div class="request-line"></div>
        <h1 class="request-title">申請一覧</h1>
    </div>

    <div class="request-tabs">
        <h2 class="request-tab active" data-tab="pending">承認待ち</h2>
        <h2 class="request-tab" data-tab="approved">承認済み</h2>
    </div>

    <table class="request-table">
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
        <tbody id="pending-data">
            <tr>
                <td>承認待ち</td>
                <td>西伶奈</td>
                <td>2025/12/01</td>
                <td>遅延のため</td>
                <td>2025/12/02</td>
                <td class="request-detail-cell">詳細</td>
            </tr>
            <tr>
                <td>承認待ち</td>
                <td>西伶奈</td>
                <td>2025/12/03</td>
                <td>体調不良</td>
                <td>2025/12/04</td>
                <td class="request-detail-cell">詳細</td>
            </tr>
        </tbody>

        <tbody id="approved-data" style="display:none;">
            <tr>
                <td>承認済み</td>
                <td>西伶奈</td>
                <td>2025/12/09</td>
                <td>遅延のため</td>
                <td>2025/12/10</td>
                <td class="request-detail-cell">詳細</td>
            </tr>
            <tr>
                <td>承認済み</td>
                <td>西伶奈</td>
                <td>2025/12/10</td>
                <td>体調不良</td>
                <td>2025/12/11</td>
                <td class="request-detail-cell">詳細</td>
            </tr>
        </tbody>
    </table>

    <script>
        document.querySelectorAll('.request-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.request-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                if (tab.dataset.tab === 'pending') {
                    document.getElementById('pending-data').style.display = '';
                    document.getElementById('approved-data').style.display = 'none';
                } else {
                    document.getElementById('pending-data').style.display = 'none';
                    document.getElementById('approved-data').style.display = '';
                }
            });
        });
    </script>

</div>

@endsection