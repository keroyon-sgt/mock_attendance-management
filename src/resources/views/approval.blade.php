@extends('layouts.default')

@section('title',$title)

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css')}}?d={{str_pad(rand(0,99999999),8,0, STR_PAD_LEFT)}}">
@endsection

@section('content')

@include('components.header')
<div class="container">

    <h1 class="detail__heading content__heading">{{ $title }}</h1>


    <div class="detail__inner">

        <div class="confirm-table">
            <table class="confirm-table__inner">

                <tr class="confirm-table__row">
                    <th class="confirm-table__header">状態</th>
                    <td class="confirm-table__text">
                        {{ $sheet['status'] }}
                    </td>
                </tr>


                <tr class="confirm-table__row">
                    <th class="confirm-table__header">名前</th>
                    <td class="confirm-table__text">
                        {{ $user->name }}
                    </td>
                </tr>
                <tr class="confirm-table__row">
                    <th class="confirm-table__header">対象日時</th>
                    <td class="confirm-table__text">
                        {{ $sheet['year'] }}年   {{ $sheet['date'] }}
                    </td>
                </tr>

                <tr class="confirm-table__row">
                    <th class="confirm-table__header">申請理由</th>
                    <td class="confirm-table__text">
                        {{ $list->remark }}
                    </td>
                </tr>

                <tr class="confirm-table__row">
                    <th class="confirm-table__header">申請日時</th>
                    <td class="confirm-table__text">
                        {{ $sheet['punch_in'] }} ～ {{ $sheet['punch_out'] }}
                    </td>
                </tr>

                <tr class="confirm-table__row">
                    <th class="confirm-table__header">詳細</th>
                    <td class="confirm-table__text">
                        {{ $sheet['punch_in'] }}
                    </td>
                </tr>

            </table>
        </div>
    </div>
</div>
@endsection