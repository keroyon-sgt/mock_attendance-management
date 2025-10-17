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

        // $user = $request->only([
        //     'name',
        //     'email',
        //     'password',
        //     // 'password' => Hash::make($request->password),
        // ]);
        // $user['password']=Hash::make($request->password);

// echo '<br />user = ';
// var_dump($user);
// echo '<br />Hash:make = ';
// var_dump(Hash::make($request->password));
// echo '<br />register ';
// exit;

        // User::create($user);


//---------------
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

    public function userRoll()   //profileRequest
    {
echo __FUNCTION__;
echo '<br /><br />get = ';
var_dump($_GET);
echo '<br /><br />post = ';
var_dump($_POST);

        $admin = Auth::user();

        if ( Auth::check() && $admin->status === 2 ) {
            $users = User::where('status','>', 0)->get();
        } else {
            // return redirect('/login');

            // return redirect('/logout');
            echo '<br /><br />logout ';
            $users =array();

        }

    return view('users',compact('users'));

    }

    //-------------------------------------------------

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


exit;


        return redirect('/profile')->with('message', '認証しました');
    }

//     public function profileForm()
//     {
//         if (Auth::check()) {
//             $user = Auth::user(); // ログインユーザーのモデルを取得
//         } else {
//             return redirect('/login'); // 未ログインの場合はログインページへリダイレクト
//         }

//         if(!$user->portrait_path){ $user->portrait_path='unknown.jpg'; }
// echo '<br />profileForm ';
// // echo '<br /><br />get = ';
// // var_dump($_GET);
// // echo '<br /><br />post = ';
// // var_dump($_POST);

//         return view('profile', compact('user'));
//     }

//     public function profileSave(Request $request)   //profileRequest
//     {
//         if (Auth::check()) {
//             $user = Auth::user(); // ログインユーザーのモデルを取得
//         } else {
//             return redirect('/login'); // 未ログインの場合はログインページへリダイレクト
//         }
        
//         if($request->file('portrait')){
//             $image_path = $request->file('portrait')->store('public');
//             $user->portrait_path = basename($image_path);
//         }
        
// // echo '<br />image_path = ';
// // var_dump(basename($image_path));
// // exit;

//         $user->name = $request->name;
//         $user->postal_code = $request->postal_code;
//         $user->address = $request->address;
//         $user->building = $request->building;

//         if( $user->isDirty() ){ $user->save(); }
        
//         // return back();
//         return redirect('mypage');
//     }

}
