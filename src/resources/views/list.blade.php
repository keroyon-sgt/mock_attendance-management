@extends('layouts.default')

@section('title', $title)

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css')}}?d={{str_pad(rand(0,99999999),8,0, STR_PAD_LEFT)}}">
@endsection

@section('content')

@include('components.header')
<div class="container">

    <h1 class="list__heading content__heading">{{ $title }}</h1>
    <div class="list__search">
        <div>
            <a href="/{{ $period_path }}?period={{ $month_last }}">←前月</a>
        </div>
        <form id="calendar-form" class="search-form" action="/{{ Request::path() }}" method="get">
            <!-- csrf -->
            <input id="calendar" class="search-form__month" type="month" name="period" value="{{$period}}">
                <button type="submit" style="display: none;"></button>
        </form>
        <div>
            <a href="/{{ $period_path }}?period={{ $month_next }}">翌月→</a>
        </div>
    </div>

    <table class="list__table">
        <tr class="list__row">
        <th class="list__label-name">日付</th>
        <th class="list__label-time">出勤</th>
        <th class="list__label-time">退勤</th>
        <th class="list__label-time">休憩</th>
        <th class="list__label-time">合計</th>
        <th class="list__label-detail">詳細</th>
        </tr>
        @foreach($monthly_list as $each_day)
        <tr class="list__row">
        <td class="list__data">{{$each_day['date']}}</td>
        <td class="list__data">{{$each_day['punch_in']}}</td>
        <td class="list__data">{{$each_day['punch_out']}}</td>
        <td class="list__data">{{$each_day['break']}}</td>
        <td class="list__data">{{$each_day['time_worked']}}</td>
        <td class="list__data">
            @if($each_day['id'])
                <a class="list__detail-btn bold" href="{{$detail_path}}{{$each_day['id']}}">詳細</a>
            <!-- else
                詳細 -->
            @endif
        </td>
        </tr>
        @endforeach
    </table>
@if($admin_mode)
    <form class="form" action="{{ Request::url() }}" method="post">
@csrf
        <input type="hidden" name="user_id" value="{{ $user_id }}" />
        <input type="hidden" name="period" value="{{ $period }}" />
        <div class="form__button">
            <button class="form__button-submit" type="submit">CSV出力</button>
        </div>
    </form>
@endif
</div>
<script>
const dateInput = document.getElementById('calendar');
const form = document.getElementById('calendar-form');

dateInput.addEventListener('change', function() {
  // フォームを送信
    form.submit();
});
</script>
@endsection