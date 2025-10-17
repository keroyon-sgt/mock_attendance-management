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
                <div class="list__table-outer">
                    <table class="list__table">
                        <tr class="list__table-row">
                            <th class="list__table-header">名前</th>
                            <td class="list__table-text">
                                {{ $user->name }}
                                <input type="hidden" name="name" value="{{ $user->name }}" />
                            </td>
                        </tr>
                        <tr class="list__table__row">
                            <th class="list__table-header">日付</th>
                            <td class="list__table-text">
                                <div class="list__flex">
                                    <div>{{ $sheet['year'] }}年</div><div>{{ $sheet['date'] }}</div>
                                </div>
                            </td>
                        </tr>
                        <tr class="list__table__row">
                            <th class="list__table-header">出勤・退勤</th>
                            <td class="list__table-time">
                                <input type="text" name="punch_in" value="{{ $sheet['punch_in'] }}" />
                                ～
                                <input type="text" name="punch_out" value="{{ $sheet['punch_out'] }}" />
                            </td>
                        </tr>
                        <tr class="list__table__row">
                            <th class="list__table-header">休憩</th>
                            <td class="list__table-time">
                                <input type="text" name="break1_in" value="{{ $sheet['break1_in'] }}" />
                                ～
                                <input type="text" name="break1_out" value="{{ $sheet['break1_out'] }}" />
                            </td>
                        </tr>
                        <tr class="list__table__row">
                            <th class="list__table-header">休憩２</th>
                            <td class="list__table-time">
                                <input type="text" name="break2_in" value="{{ $sheet['break2_in'] }}" />
                                ～
                                <input type="text" name="break2_out" value="{{ $sheet['break2_out'] }}" />
                        </tr>
                        <tr class="list__table__row">
                            <th class="list__table-header">備考</th>
                            <td class="list__table-remark">
                                <!-- {{ $list['detail'] }}kakikaeru text->textarea
                                <input type="text" name="detail" value="{{ $list['detail'] }}" readonly /> -->
                                <!-- <textarea name="remark">{{ $list->remark }}</textarea> -->
                                <input type="text" name="remark" value="{{ $list->remark }}" />
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="form__button">
                    <button class="form__button-submit" type="submit">修正</button>
                </div>
            </form>

    </div>
</div>
@endsection