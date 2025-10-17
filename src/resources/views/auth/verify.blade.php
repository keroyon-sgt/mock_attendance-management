@extends('layouts.default')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/verify.css')  }}?d={{str_pad(rand(0,99999999),8,0, STR_PAD_LEFT)}}">
@endsection

@section('content')
@include('components.header')
<div class="mail_notice--div">
    <div class="mail_notice--header">
        <p class="notice_header--p">メール認証はお済みですか？</p>
    </div>

    <div class="mail_notice--content">
        @if (session('resent'))
        <p class="notice_resend--p" role="alert">
            新規認証メールを再送信しました！
        </p>
        @endif
        <p class="alert_resend--p">
            このページを閲覧するには、Eメールによる認証が必要です。
            もし認証用のメールを受け取っていない場合、
            <form class="mail_resend--form" method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="mail_resend--button">こちらのリンク</button>をクリックして、認証メールを受け取ってください。
            </form>
        </p>
    </div>
</div>


<div class="register-form__content">
    <div class="register-form__heading">
        <p>登録していただいたメールアドレスに認証メールを送付しました。<br />メール認証を完了してください。</p>
    </div>
    <form class="form" action="{{ route('verification.send') }}" method="get">
        @csrf
        <div class="form__button">
            <button class="form__button-submit" type="submit">認証はこちらから</button>
        </div>
    </form>
    <div class="resend__link">
        <a href="{{ route('verification.send') }}">認証メールを再送する</a>
    </div>
</div>
@endsection