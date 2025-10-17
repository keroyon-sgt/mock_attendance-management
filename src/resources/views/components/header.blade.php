<header class="header">
    <div class="header_inner">
        <div class="header__logo">
            <a href="/"><img src="{{ asset('img/logo.png') }}" alt="ロゴ"></a>
        </div>
        @if( !in_array(Route::currentRouteName(), ['register', 'login', 'verification.notice']) )
        <nav class="header__nav">
            <ul>
                @if(Auth::check())
                    @if($admin_mode)
                <li><a href="/admin/attendances">勤怠一覧</a></li>
                <li><a href="/admin/users">スタッフ一覧</a></li>
                <li><a href="/admin/requests">申請一覧</a></li>
                    @else
                <li><a href="/attendance">勤怠</a></li>
                <li><a href="/attendance/list">勤怠一覧</a></li>
                <li><a href="/stamp_correction_request/list">申請</a></li>
                    @endif
                <li>
                    <form action="/logout" method="post">
                        @csrf
                        <button class="header__logout">ログアウト</button>
                    </form>
                </li>
                @else
                <li><a href="/login">ログイン</a></li>
                <li><a href="/register">会員登録</a></li>
                @endif
            </ul>
        </nav>
        @endif
    </div>
</header>