@extends('layouts.default')

@section('title', $title)

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css')}}?d={{str_pad(rand(0,99999999),8,0, STR_PAD_LEFT)}}">
@endsection

@section('content')

@include('components.header')
<div class="container">

    <h1 class="detail__heading content__heading">{{ $title }}</h1>
    <div class="detail__inner">
<form class="form" action="{{ Request::url() }}" method="post">
@csrf
<input type="hidden" name="sheet_id" value="{{ $list->id }}" />
                <div class="list__table_outer">
                    <table class="list__table">
                        <tr class="list__table__row">
                            <th class="list__table__header">名前</th>
                            <td class="list__table-text">
                                {{ $user->name }}
                            </td>
                        </tr>
                        <tr class="list__table__row">
                            <th class="list__table__header">日付</th>
                            <td class="list__table-text">
                                {{ $sheet['year'] }}年   {{ $sheet['date'] }}
                            </td>
                        </tr>
                        <tr class="list__table__row">
                            <th class="list__table__header">出勤・退勤</th>
                            <td class="list__table-text">
                                {{ $sheet['punch_in'] }} ～ {{ $sheet['punch_out'] }}
                            </td>
                        </tr>
                        <tr class="list__table__row">
                            <th class="list__table__header">休憩</th>
                            <td class="list__table-text">
                                {{ $sheet['break1_in'] }} ～ {{ $sheet['break1_out'] }}
                            </td>
                        </tr>
                        <tr class="list__table__row">
                            <th class="list__table__header">休憩２</th>
                            <td class="list__table-text">
                                {{ $sheet['break2_in'] }} ～ {{ $sheet['break2_out'] }}
                        </tr>
                        <tr class="list__table__row">
                            <th class="list__table__header">備考</th>
                            <td class="list__table-text">
                                {{ $list->remark }}
                            </td>
                        </tr>
                    </table>
                </div>
            @if($admin_mode)
                <div class="form__button">
                @if($list['status']=== 1 )
                    <!-- <form class="form" action="{{ Request::url() }}" method="post"> -->
                        <!-- @csrf -->
                        <!-- <input type="hidden" name="sheet_id" value="{{ $list->id }}" /> -->
                    <button class="form__button-submit" type="submit">承認</button>

                    <!-- </form> -->
                @elseif($list['status']=== 2 )
                    <!-- <div class="form__button-invalid">承認済</div> -->
                    <input type="hidden" name="status" value="2" />
                    <button class="form__button-invalid" type="disabled">承認済</button>
                @else
                    <a class="list__detail-btn" href="/admin/attendances/{{$list['id']}}">修正画面</a>
                @endif
                </div>
            @else
                <div class="list__red">※承認待ちのため修正はできません。</div>
            @endif
            </form>

    </div>
</div>
@endsection