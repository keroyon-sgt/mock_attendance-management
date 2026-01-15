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
            <input type="hidden" name="id" value="{{ $list->id }}" />
            <div class="list__table-outer">
                <table class="list__table">
                    <tr class="list__table-row">
                        <th class="list__table-header">名前</th>
                        <td class="list__table-text">
                            {{ $user->name }}
                            <input type="hidden" name="user_id" value="{{ $user->id }}" />
                            <input type="hidden" name="name" value="{{ $user->name }}" />
                        </td>
                    </tr>
                    <tr class="list__table__row">
                        <th class="list__table-header">日付</th>
                        <td class="list__table-text">
                            <div class="list__flex">
                                <div>{{ $sheet['year'] }}年</div>
                                <div>{{ $sheet['date'] }}</div>
                            </div>
                        </td>
                    </tr>
                    <tr class="list__table__row">
                        <th class="list__table-header">出勤・退勤</th>
                        <td class="list__table-time">
                            <input type="text" name="punch_in" value="{{ old('punch_in', $sheet['punch_in'] ) }}" />
                            ～
                            <input type="text" name="punch_out" value="{{ old('punch_out', $sheet['punch_out'] ) }}" />
                            <div class="form__error">
                                @error('punch')
                                {{ $message }}
                                @enderror
                            </div>
                        </td>
                    </tr>
                    <?php $break_count = 1;   ?>
                    @foreach($sheet['breaks'] as $break_id => $each_brake)

                    <tr class="list__table__row">
                        <th class="list__table-header">休憩{{ $break_count }}</th>
                        <td class="list__table-time">
                            <input type="text" name="break[{{ $break_id}}][in]" value="{{ old('break.'.$break_id.'.in', $each_brake['in']) }}" />
                            ～
                            <input type="text" name="break[{{ $break_id }}][out]" value="{{ old('break.'.$break_id.'.out', $each_brake['out']) }}" />
                            <div class="form__error">
                                @error('break_'.$break_id)
                                {{ $message }}
                                @enderror
                            </div>
                        </td>
                    </tr>
                    <?php $break_count++ ?>
                    @endforeach

                    <tr class="list__table__row">
                        <th class="list__table-header">休憩{{ $break_count }}</th>
                        <td class="list__table-time">
                            <input type="text" name="break[new][in]" value="{{ old('break.new.in') }}" />
                            ～
                            <input type="text" name="break[new][out]" value="{{ old('break.new.out') }}" />
                            <div class="form__error">
                                @error('break_new')
                                {{ $message }}
                                @enderror
                            </div>
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