@extends('layouts.default')

@section('title',$title)

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}?d={{str_pad(rand(0,99999999),8,0, STR_PAD_LEFT)}}">
@endsection

@section('content')

@include('components.header')
    <div class="container">
        <div class="user_status">{{ $situation_text }}</div>
        <div class="date">{{ $today }}</div>
        <time class="clock" id="clock">{{ $clock }}</time>

        <div class="form__outer">

    @switch($situation)
        @case(0)

            <form class="form" action="/attendance" method="post">
                @csrf
                <input type="hidden" name="punch" value="punch_in" />
                <div class="form__button">
                    <button class="form__button-black" type="submit">出勤</button>
                </div>
            </form>

        @break

        @case(1)

            <form class="form" action="/attendance" method="post">
                @csrf
                <input type="hidden" name="punch" value="punch_out" />
                <div class="form__button">
                    <button class="form__button-black" type="submit">退勤</button>
                </div>
            </form>
            <form class="form" action="/attendance" method="post">
                @csrf
                <input type="hidden" name="punch" value="break1_in" />
                <div class="form__button">
                    <button class="form__button-white" type="submit">休憩入</button>
                </div>
            </form>

        @break


        @case(2)

            <form class="form" action="/attendance" method="post">
                @csrf
                <input type="hidden" name="punch" value="break1_out" />
                <div class="form__button">
                    <button class="form__button-white" type="submit">休憩戻</button>
                </div>
            </form>

        @break

        @case(3)

            <form class="form" action="/attendance" method="post">
                @csrf
                <input type="hidden" name="punch" value="punch_out" />
                <div class="form__button">
                    <button class="form__button-black" type="submit">退勤</button>
                </div>
            </form>
            <form class="form" action="/attendance" method="post">
                @csrf
                <input type="hidden" name="punch" value="break2_in" />
                <div class="form__button">
                    <button class="form__button-white" type="submit">休憩入</button>
                </div>
            </form>

        @break

        @case(4)

            <form class="form" action="/attendance" method="post">
                @csrf
                <input type="hidden" name="punch" value="break2_out" />
                <div class="form__button">
                    <button class="form__button-white" type="submit">休憩戻</button>
                </div>
            </form>

        @break

        @case(5)

            <form class="form" action="/attendance" method="post">
                @csrf
                <input type="hidden" name="punch" value="punch_out" />
                <div class="form__button">
                    <button class="form__button-black" type="submit">退勤</button>
                </div>
            </form>

        @break

        @case(6)

            <div class="form__text">お疲れ様でした。</div>

        @break
    @endswitch

        </div>
    </div>


<script type="text/javascript">

    setInterval('showClock()',1000);

    function showClock() {
        let now = new Date();
        let nowHour = now.getHours();
        let nowMin  = now.getMinutes();
        let nowSec  = now.getSeconds();

        let msg = String(nowHour).padStart(2, "0") + ":" + String(nowMin).padStart(2, "0");   // + ":" + nowSec

        document.getElementById("clock").innerHTML = msg;
    }
    // setInterval('showClock()',1000);

</script>




@endsection