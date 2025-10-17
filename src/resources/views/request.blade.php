@extends('layouts.default')

@section('title', $title)

@section('css')
<link rel="stylesheet" href="{{ asset('css/request.css')}}?d={{str_pad(rand(0,99999999),8,0, STR_PAD_LEFT)}}">
@endsection

@section('content')

@include('components.header')
<div class="container">

    <h1 class="list__heading content__heading">{{ $title }}</h1>
    <div class="list__tab-outer">
        <div class="list__tab{{$bold_1}}">
            <a href="/{{ Request::path() }}?status=1  ">承認待ち</a>
        </div>

        <div class="list__tab{{$bold_2}}">
            <a href="/{{ Request::path() }}?status=2  ">承認済み</a>
        </div>
    </div>

    <table class="list__table">
        <tr class="list__row">
        <th class="list__label-status">状態</th>
        <th class="list__label-name">名前</th>
        <th class="list__label-date">対象日時</th>
        <th class="list__label-remark">申請理由</th>
        <th class="list__label-date">申請日時</th>
        <th class="list__label-detail">詳細</th>
        </tr>
        @foreach($request_list as $each_request)
        <tr class="list__row">
        <td class="list__data">{{$each_request['status']}}</td>
        <td class="list__data">{{$each_request['name']}}</td>
        <td class="list__data">{{$each_request['date']}}</td>
        <td class="list__data">{{$each_request['remark']}}</td>
        <td class="list__data">{{$each_request['updated_at']}}</td>
        <td class="list__data bold">
            @if($each_request['id'])
                <a class="list__detail-btn" href="/admin/requests/{{$each_request['id']}}">詳細</a>
            @else
                詳細
            @endif
        </td>
        </tr>
        @endforeach
    </table>
</div>
@endsection