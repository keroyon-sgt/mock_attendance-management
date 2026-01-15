<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

use Illuminate\Http\Request;

use App\Models\User;

use App\Http\Requests\LoginRequest;



class AdminController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        /* Database Insert */
        $user = $request->only([
            'email',
            'password',
        ]);

        // echo '<br /><br />user = ';
        // var_dump($user);
        // // exit;

        if (Auth::guard('admins')->attempt($user)) {
            $request->session()->regenerate();
            // dd($request->session()->all());

            // var_dump(Auth::check());
            // var_dump(Auth::guard('admins')->check());
            // exit;


            // return redirect()->route('admin.index')->with('message', 'ログインしました');
            return redirect('admin/users')->with('message', 'ログインしました');
        }

        return back()->with('message', 'メールアドレスかパスワードが間違っています。');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('message', 'ログアウトしました');
    }
}
