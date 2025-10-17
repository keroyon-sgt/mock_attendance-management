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
            <a href="/{{ $date_path }}?date={{ $date_last }}">←前日</a>
        </div>
        <form id="calendar-form" class="search-form" action="/{{ Request::path() }}" method="get">
            @csrf
            <input id="calendar" class="search-form__date" type="date" name="date" value="{{$date}}">

            <!-- <div class="search-form__actions"> -->
                <!-- <input class="search-form__search-btn btn" type="submit" value="検索"> -->
                <button type="submit" style="display: none;"></button>
            <!-- </div> -->
        </form>
        <div>
            <a href="/{{ $date_path }}?date={{ $date_next }}">翌日→</a>
        </div>
    </div>

    <table class="list__table">
        <tr class="list__row">
        <th class="list__label-name">名前</th>
        <th class="list__label-time">出勤</th>
        <th class="list__label-time">退勤</th>
        <th class="list__label-time">休憩</th>
        <th class="list__label-time">合計</th>
        <th class="list__label-detail">詳細</th>
        </tr>
        @foreach($daily_list as $each_sheet)
        <tr class="list__row">
        <td class="list__data">{{$each_sheet['name']}}</td>
        <td class="list__data">{{$each_sheet['punch_in']}}</td>
        <td class="list__data">{{$each_sheet['punch_out']}}</td>
        <td class="list__data">{{$each_sheet['break']}}</td>
        <td class="list__data">{{$each_sheet['time_worked']}}</td>
        <td class="list__data">
            @if($each_sheet['id'])
                <a class="list__detail-btn bold" href="{{$detail_path}}{{$each_sheet['id']}}">詳細</a>
            <!-- else
                詳細 -->
            @endif
        </td>
        </tr>
        @endforeach
    </table>
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