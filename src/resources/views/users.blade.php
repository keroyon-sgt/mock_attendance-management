@extends('layouts.default')

@section('title','勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/users.css')}}?d={{str_pad(rand(0,99999999),8,0, STR_PAD_LEFT)}}">
@endsection

@section('content')

@include('components.header')
<div class="container">

    <h1 class="list__heading content__heading">スタッフ一覧</h1>

    <table class="list__table">
        <tr class="list__row">
        <th class="list__label-name">名前</th>
        <th class="list__label-email">メールアドレス</th>
        <th class="list__label-detail">月次勤怠</th>
        </tr>
        @foreach($users as $each_user)
        <tr class="list__row">
        <td class="list__data-name">{{$each_user['name']}}</td>
        <td class="list__data-email">{{$each_user['email']}}</td>
        <td class="list__data-detail">
        <a class="list__detail-btn bold" href="/admin/users/{{$each_user['id']}}/attendances">詳細</a>
        </td>
        </tr>
        @endforeach
    </table>
</div>
@endsection