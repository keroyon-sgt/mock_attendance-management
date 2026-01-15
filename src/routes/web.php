<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

// use App\Providers\FortifyServiceProvider;

use Illuminate\Foundation\Auth\EmailVerificationRequest;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// Auth::routes(['verify' => true]);

// ------------------------------------------------------------
Route::prefix('admin')->group(function () {  //verified
    Route::get('/login', [AdminController::class, 'LoginForm'])->name('admin.login');;
    Route::post('/login', [AdminController::class, 'Login']);
    // Route::get('/logout', [AuthController::class, 'adminLogout']);

});

// Route::prefix('admin')->middleware('auth:admins')->group(function () {
//     Route::get('/', [IndexController::class, 'index'])->name('admin.login');
// });


Route::prefix('admin')->middleware('auth:admins')->group(function () {  //verified

    Route::get('/attendances', [TimesheetController::class, 'dailyAttendanceRoll']);
    Route::get('/attendances/{id}', [TimesheetController::class, 'detail']);
    Route::post('/attendances/{id}', [TimesheetController::class, 'update']);

    // Route::get('/users', [UserController::class, 'userRoll'])->name('admin.index');

    Route::get('/users/{user_id}/attendances', [TimesheetController::class, 'adminAttendanceRoll']);
    Route::POST('/users/{user_id}/attendances', [TimesheetController::class, 'export']);

    Route::get('/requests', [TimesheetController::class, 'requestRoll']);

    Route::get('/requests/{id}', [TimesheetController::class, 'detail']);
    Route::post('/requests/{id}', [TimesheetController::class, 'approve']);
});

Route::get('/admin/users', [UserController::class, 'userRoll'])->name('admin.index');
//----------------------------------------------------


Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/logout', [AuthController::class, 'Logout']);

Route::get('/register', [UserController::class, 'registerForm']);
Route::post('/register', [UserController::class, 'register']);

// Route::get('/verify', [UserController::class, 'verify']);


Route::middleware('verified')->group(function () { //middleware('verified')or middleware('auth')
    Route::get('/', [TimesheetController::class, 'attendance']);

    Route::get('/attendance', [TimesheetController::class, 'attendance']);
    Route::post('/attendance', [TimesheetController::class, 'punch']);

    Route::get('/attendance/list', [TimesheetController::class, 'attendanceRoll']);
    Route::post('/attendance/list', [TimesheetController::class, 'attendanceRoll']);
    Route::get('/attendance/list/{period}', [TimesheetController::class, 'attendanceRoll']);

    Route::get('/attendance/detail/{id}', [TimesheetController::class, 'detail']);
    Route::post('/attendance/detail/{id}', [TimesheetController::class, 'update']);

    Route::get('/stamp_correction_request/list', [TimesheetController::class, 'requestRoll']);
});



Route::get('/email/verify', [UserController::class, 'notice'])->name('verification.notice');

Route::get('/email/verification-resend',  [UserController::class, 'resend'])->name('verification.send');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    session()->forget('unauthenticated_user');
    return redirect('/');
})->name('verification.verify');


// Route::get('/email/verify', function () {
//     return view('auth.verify-email');
// })->name('verification.notice');

// Route::post('/email/verification-notification', function (Request $request) {
//     session()->get('unauthenticated_user')->sendEmailVerificationNotification();
//     session()->put('resent', true);
//     return back()->with('message', 'Verification link sent!');
// })->name('verification.send');

// Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
//     $request->fulfill();
//     session()->forget('unauthenticated_user');
//     return redirect('/mypage/profile');
// })->name('verification.verify');
