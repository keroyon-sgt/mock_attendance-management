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

        if (!Auth::check()) {
            $user = $request->only([
                'email',
                'password',
            ]);
            if (Auth::attempt($user)) {
                $request->session()->regenerate();
            }
        }

        // // ------------------------------------------

        // $verificationUrl = $this->verificationUrl($notifiable);

        // $notifiable = 6 ;
        // $verificationUrl = $this->verificationUrl($notifiable);

        // dd(sha1($user->getEmailForVerification()));

        $user = Auth::user();

        $data = [
            'admin_mode' => FALSE,
            'id' => $user->id,
            'hash' => sha1($user->email),
        ];

        Mail::send('mail.verify', $data, function ($message) use ($request) {
            $message->to($request->email, $request->name)
                ->subject('メール認証');
        });

        // $user->sendEmailVerificationNotification();
        //------------------------------------------
        // exit;
        // return back();
        // return redirect('mail/verify')->with('message', '登録しました');
        return redirect('/')->with('message', '登録しました');
    }

    public function notice()
    {

        if (Auth::check()) {

            $user = Auth::user();

            $admin_mode = FALSE;
            $id = $user->id;
            // $hash = sha1($user->getEmailForVerification());
            $hash = sha1($user->email);
            return view('auth.verify', compact('admin_mode', 'id', 'hash'));
        }

        return redirect('login');
    }


    public function resend() //LoginRequest $request
    {
        //token 作る sessionに入れる 検証処理

        if (Auth::check()) {

            $user = Auth::user();

            // echo '<br /><br />user = ';
            // var_dump($user);
            // echo '<br /><br />time_break_out = ';
            // var_dump($time_break_out);
            // echo '<br /><br />time_break = ';
            // var_dump($time_break);
            // exit;

            $data = [
                'admin_mode' => FALSE,
                'id' => $user->id,
                'hash' => sha1($user->email),
            ];

            // echo '<br /><br />data = ';
            // var_dump($data);
            // echo '<br /><br />user->email = ';
            // var_dump($user->email);
            // exit;

            Mail::send('mail.verify', $data, function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('メール認証');
            });
            session()->put('resent', true);
            return back()->with('message', 'Verification link sent!');
        }

        return redirect('login');
    }

    public function userRoll(Request $request)
    {

        $admin_mode = FALSE;

        $admin = Auth::guard('admins')->user();
        if (preg_match("/^admin/i", $request->path())) {
            if ($admin->status > 1) {
                $admin_mode = TRUE;
            } else {
                return redirect('/logout');
            }
        }

        if (Auth::guard('admins')->check() && $admin->status === 2) {
            $users = User::where('status', '>', 0)->get();
        } else {
            return redirect('/logout');
        }

        return view('users', compact('users', 'admin_mode'));
    }

    // public function verify()
    // {
    //     // メールを送る処理
    //     return view('mail.verify');
    // }

    //routeのコールバック関数を移植



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
