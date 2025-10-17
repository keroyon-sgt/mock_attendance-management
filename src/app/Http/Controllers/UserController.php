<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

use Illuminate\Http\Request;



use App\Models\User;

use App\Http\Requests\LoginRequest;

class UserController extends Controller
{
    public function registerForm()
    {
        return view('auth.register');
    }

    public function register(LoginRequest $request)
    {

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
//------------------

        // if (!Auth::check()) {
        //     $user = $request->only([
        //         'email',
        //         'password',
        //     ]);
        //     // return redirect('login'); // 未ログインの場合はログインページへリダイレクト
        //     if (Auth::attempt($user)) {
        //         $request->session()->regenerate();
        //         // return redirect()->intended('/')->with('message', 'ログインしました');
        //         return redirect('/')->with('message', 'ログインしました');
        //     }
        // }

// // ------------------------------------------

        // $verificationUrl = $this->verificationUrl($notifiable);

        // $notifiable = 6 ;
        // $verificationUrl = $this->verificationUrl($notifiable);

        $data = [];
        Mail::send('mail.verify', $data, function($message) use($request){
            $message->to($request->email, $request->name)
            ->subject('メール認証');
        });

        // $user->sendEmailVerificationNotification();
        //------------------------------------------
        // exit;
        // return back();
        return redirect('mail/verify')->with('message', '登録しました');
    }

    public function userRoll(Request $request)
    {

        $admin_mode = FALSE;

        $admin = Auth::user();
        if(preg_match("/^admin/i",$request->path())){
            if($admin->status > 1){
                $admin_mode = TRUE;
            }else{
                return redirect('/logout');
            }
        }

        if ( Auth::check() && $admin->status === 2 ) {
            $users = User::where('status','>', 0)->get();
        } else {
            return redirect('/logout');
        }

        return view('users',compact('users', 'admin_mode'));

    }

    public function verify()
    {
        // メールを送る処理
        return view('mail.verify');
    }

    public function verification(Request $request) //: RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            // return redirect()->intended(RouteServiceProvider::HOME.'?verified=1');
            return redirect('/profile')->with('message', 'すでに認証がされています');
        }

        // email_verified_atカラムの更新
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // return redirect()->intended(RouteServiceProvider::HOME.'?verified=1');
        // exit;

        return redirect('/profile')->with('message', '認証しました');
    }

}
