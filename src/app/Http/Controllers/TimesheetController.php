<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Requests\StoreTimesheetRequest;
use App\Http\Requests\UpdateTimesheetRequest;

use App\Models\Timesheet;
use App\Models\User;

use Carbon\Carbon;

// use DateTimeImmutable;

class TimesheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function attendance()
    {
// echo __FUNCTION__;

        if (!Auth::check()) { return redirect('/login'); }

        $user = Auth::user();

        $today = now()->format('Y-m-d');

        // $today = Carbon::tomorrow()->format('Y-m-d');

        $clock=now()->format('H:i');
        // $clock=now()->format('H:i:s');

        $sheet_today = Timesheet::where('user_id', $user->id)->where('date', $today)->latest()->orderBy('id', 'DESC')->first();


// echo '<br /><br />sheet_today->date = ';
// var_dump($sheet_today->date);
// echo '<br /><br />today = ';
// var_dump($today);


        if($sheet_today){
            if($sheet_today->date === $today){

                // if(!$sheet_today->break1_in || ($sheet_today->break1_in&&$sheet_today->break1_out)){
                //     $situation = '1';
                // }elseif(($sheet_today->break1_in&&$sheet_today->break1_out)||($sheet_today->break2_in&&$sheet_today->break2_out)){
                //     $situation = '2';
                // }elseif($sheet_today->punch_out){
                //     $situation = '3';
                // }
// echo '<br /><br />today あるよ！ ';

                if($sheet_today->punch_out){
                        $situation = '6';
                        $situation_text = '退勤済';
                }else{
                    if($sheet_today->break1_in){
                        if($sheet_today->break1_out){
                            if($sheet_today->break2_in){
                                if($sheet_today->break2_out){
                                    $situation = '5';
                                    $situation_text = '勤務中';
                                }else{
                                    $situation = '4';
                                    $situation_text = '休憩中';
                                }
                            }else{
                                $situation = '3';
                                $situation_text = '勤務中';
                            }
                        }else{
                            $situation = '2';
                            $situation_text = '休憩中';
                        }
                    }else{
                        $situation = '1';
                        $situation_text = '勤務中';
                    }
                }
            }

// echo '<br /><br />situation = ';
// var_dump($situation);
// echo '<br /><br />sheet_today->punch_in = ';
// var_dump($sheet_today->punch_in);
// echo '<br /><br />sheet_today->punch_out = ';
// var_dump($sheet_today->punch_out);
// echo '<br /><br />sheet_today->break1_in = ';
// var_dump($sheet_today->break1_in);
// echo '<br /><br />sheet_today->break1_out = ';
// var_dump($sheet_today->break1_out);
// echo '<br /><br />sheet_today->break2_in = ';
// var_dump($sheet_today->break2_in);
// echo '<br /><br />sheet_today->break2_out = ';
// var_dump($sheet_today->break2_out);

        }else{
            // echo '<br /><br />sheet ないよ！ ';
            $situation = '0';
            $situation_text = '勤務外';
        }

// echo '<br /><br />2025-01-01 = ';
// var_dump(Carbon::parse('2025-01-01')->isoFormat('YYYY年M月D日(ddd)'));

// echo '<br /><br />2025-12-25 = ';
// var_dump(Carbon::parse('2025-12-25')->isoFormat('YYYY年M月D日(ddd)'));

        // $today =  Carbon::parse($today)->format('Y年m月d日(D)');
        $today =  Carbon::parse($today)->isoFormat('YYYY年M月D日(ddd)'); //->formatLocalized('m月j(D)');
        $title = '出勤登録';

        // return view('index',compact('items','tab', 'search'));
        return view('attendance',compact('situation', 'situation_text', 'today', 'clock', 'title'));
    }

//-------------------------------------------------------------------------


    public function punch(Request $request)
    {

// echo __FUNCTION__;
// echo '<br /><br />get = ';
// var_dump($_GET);
// echo '<br /><br />post = ';
// var_dump($_POST);

//         switch($request->punch){
//             case 'punch_in':
//                 Timesheet::create([
//                     'user_id' => Auth::id(),
//                     'date' => today(),
//                     'punch_in' => now()->format('H:i:s'),
//                 ]);
// // echo '<br /><br />Auth:id = ';
// // var_dump(Auth::id());
// // echo '<br /><br />today = ';
// // var_dump(today());
// // echo '<br /><br />time = ';
// // var_dump(now()->format('H:i:s'));

//             break;

//             case 'punch_out':
//                 $sheet_today = Timesheet::where('user_id', $user->id)->where('date', $today)->latest()->orderBy('id', 'DESC')->first();
//                 $sheet_today->punch_out = now()->format('H:i:s');
//                 if( $sheet_today->isDirty() ){ $sheet_today->save(); }
//             break;

//             case 'break_in':

//             break;

//             case 'break_out':

//             break;



//         }
// // exit;

$user_id = Auth::id();
$now = now();
$today = $now->format('Y-m-d');
$time = $now->format('H:i:s');
$punch = $request->punch;

// echo '<br /><br />Auth:id user_id = ';
// var_dump($user_id);
// echo '<br /><br />today = ';
// var_dump($today);
// echo '<br /><br />time = ';
// var_dump($time);
// echo '<br /><br />punch = ';
// var_dump($punch);

if($request->punch === 'punch_in'){
    Timesheet::create([
        'user_id' => $user_id,
        'date' => $today,
        'punch_in' => $time,
    ]);
}else{
    $sheet_today = Timesheet::where('user_id', $user_id)->where('date', $today)->latest()->orderBy('id', 'DESC')->first();
    if($sheet_today && $request->punch){
        $sheet_today->$punch = $time;       //punch → punch selector → $selector に変更
        if( $sheet_today->isDirty() ){ $sheet_today->save(); }
    }
}
        return redirect('/');

    }


    public function attendanceRoll( Request $request )//$period,
    {
// echo __FUNCTION__;
// echo '<br /><br />get = ';
// var_dump($_GET);
// echo '<br /><br />post = ';
// var_dump($_POST);


        $user_id = Auth::id();
        $title = '勤怠一覧';
        $detail_path = '/attendance/detail/';

        $result = $this -> monthlyRoll( $user_id, $request );

$monthly_list = $result['monthly_list'];
$period = $result['period'];
$days = $result['days'];
$period_path = $result['period_path'];
$month_last = $result['month_last'];
$month_next = $result['month_next'];


        return view('list',compact('monthly_list', 'period', 'title', 'days', 'period_path', 'detail_path', 'month_last', 'month_next')); ///'user', 


    }

    //-------------------------------------------------------------------

    public function adminAttendanceRoll( $user_id, Request $request )//$period,
    {
// echo __FUNCTION__;
// echo '<br /><br />get = ';
// var_dump($_GET);
// echo '<br /><br />post = ';
// var_dump($_POST);


$admin = Auth::user();
if($admin->status <= 1 && preg_match("/^admin/i",$request->path())){
    // return redirect('/logout');
    echo '<br /><br />logout ';
}

        $user = user::find($user_id);
        $title = $user->name.'さんの勤怠';
        $detail_path = '/admin/attendances/';

        $result = $this -> monthlyRoll( $user_id, $request );

echo '<br /><br />RETURNED from Roll = ';

$monthly_list = $result['monthly_list'];
$period = $result['period'];
$days = $result['days'];
$period_path = $result['period_path'];
$month_last = $result['month_last'];
$month_next = $result['month_next'];


        return view('list',compact('monthly_list', 'period', 'title', 'days', 'period_path', 'detail_path', 'month_last', 'month_next')); ///'user', 

    }

//----------------------------------------------------------------

    public function dailyAttendanceRoll( Request $request )//$period,
    {
// echo __FUNCTION__;
// echo '<br /><br />get = ';
// var_dump($_GET);
// echo '<br /><br />post = ';
// var_dump($_POST);


$user = Auth::user();
if($user->status <= 1 && preg_match("/^admin/i",$request->path())){
    // return redirect('/logout');
    echo '<br /><br />logout ';
}

if($request->date){

    $date_formatted = str_replace('-', '/', $request->date);

}else{
    $date_formatted = now()->format('Y/m/d');
}

        $title = $date_formatted.'の勤怠';
        $detail_path = '/admin/attendances/';

        $result = $this -> dailyRoll( $request );

// echo '<br /><br />RETURNED from Roll  ';

// echo '<br /><br />result = ';
// var_dump($result);

$daily_list = $result['daily_list'];
$date = $result['date'];
$date_path = $result['date_path'];
$date_last = $result['date_last'];
$date_next = $result['date_next'];


        return view('list_daily',compact('daily_list', 'date', 'title', 'date_path', 'detail_path', 'date_last', 'date_next')); ///'user', 

    }

    //--------------------------------------------------------------------------

    public function monthlyRoll( $user_id, Request $request )//$period,protected 
    {
// echo '<br /><br />';
// echo __FUNCTION__;
// echo '<br /><br />get = ';
// var_dump($_GET);
// echo '<br /><br />post = ';
// var_dump($_POST);

    // if(!$user_id){
    //     $user_id = Auth::id();
    //     $title = '勤怠一覧';
    //     $detail_path = '/attendance/detail/';
    // }else{
    //     $user = user::find($user_id);
    //     $title = $user->name.'さんの勤怠';
    //     $detail_path = '/admin/attendances/';
    // }

    if(!$period = $request->period){   // = $request->period
        $period_raw = Carbon::now();
        $period = $period_raw->format('Y-m');
    }else{
        $period_raw = Carbon::parse($period);
    }

// echo '<br /><br />period = ';
// var_dump($period);

// echo '<br /><br />user_id = ';
// var_dump($user_id);

// echo '<br /><br />user = ';
// var_dump($user);

$year = $period_raw->format('Y');
$month = $period_raw->format('m');


// echo '<br /><br />year = ';
// var_dump($year);
// echo '<br /><br />month = ';
// var_dump($month);

$month_last = $period_raw->subMonth()->format('Y-m');
$month_next = $period_raw->addMonth(2)->format('Y-m');

// echo '<br /><br />month_last_raw = ';
// var_dump($period_raw->format('Y-m-d'));
// echo '<br /><br />month_last_raw = ';
// var_dump($period_raw->format('Y-m-d'));

// echo '<br /><br />month_last_raw = ';
// var_dump($period_raw->subMonth());
// echo '<br /><br />month_next_raw = ';
// var_dump($period_raw->addMonth());


// echo '<br /><br />month_last = ';
// var_dump($month_last);
// echo '<br /><br />month_next = ';
// var_dump($month_next);


$days = Carbon::create($year, $month, 1)->daysInMonth;
// echo '<br /><br />days = ';
// var_dump($days);

$list = Timesheet::
where('user_id', $user_id)->
where('date', 'LIKE', "%{$period}%")->
get();

// echo '<br /><br />list = ';
// var_dump($list->toArray());
// echo '<br /><br />list count() = ';
// var_dump($list->count());


$format_source = 'Y-m-d';
$format_period = 'm/d()';

$monthly_list = array();
for($i = 1; $i <= $days; $i++){

// $day = sprintf('%02d', $i);

$day=Carbon::create($year, $month, $i);

$date_key = $day->isoFormat('YYYY-MM-DD');
$date_formatted = $day->isoFormat('MM/DD(ddd)'); //->formatLocalized('m月j(D)');

// echo '<br /><br />date_key = ';
// var_dump($date_key);
// echo '<br /><br />date_formatted = ';
// var_dump($date_formatted);
// var_dump(now()->formatLocalized('%m/%d(%a)'));
// var_dump(now()->isoFormat('MM/DD(ddd)'));

// echo '<br /><br />list->day = ';
// var_dump($list->$day);

    $monthly_list[$date_key] = [
        'id' => '',
        'date' => $date_formatted,
        'punch_in' => '',
        'punch_out' => '',
        'break' => '',
        'time_worked' => '',
    ];
}
// echo '<br /><br />monthly_list = ';
// var_dump($monthly_list);

foreach($list as $each_day){

    // echo '<br /><br />each_day->date = ';
    // var_dump($each_day->date);

    // echo '<br /><br />each_day->break2 = ';
    // var_dump($each_day->break2_out);

    // $time_start = Carbon::createFromFormat('H:i:s', $each_day->punch_in);
    // $time_end = Carbon::createFromFormat('H:i:s', $each_day->punch_out);

    $time_start = Carbon::parse($each_day->date.' '.$each_day->punch_in)->format('U');
    $time_end = Carbon::parse($each_day->date.' '.$each_day->punch_out)->format('U');

    // echo '<br /><br />time_start = ';
    // var_dump($time_start->format('H時間i分'));
    // echo '<br /><br />time_end = ';
    // var_dump($time_end->format('H時間i分'));
    //  echo '<br /><br />time_end = ';
    // var_dump($time_end->format('U'));

    // echo '<br /><br />time_start = ';
    // var_dump($time_start);
    // echo '<br /><br />time_end = ';
    // var_dump($time_end);


    if($each_day->break1_in && $each_day->break1_out){
    $time_break1_in = Carbon::createFromFormat('H:i:s', $each_day->break1_in)->format('U');
    $time_break1_out = Carbon::createFromFormat('H:i:s', $each_day->break1_out)->format('U');
    // $time_break1 = $time_break1_out->diffAsCarbonInterval($time_break1_in);
    $time_break1 = $time_break1_out - $time_break1_in;
    }else{
        $time_break1 = NULL;
    }

    // echo '<br /><br />time_break1 = ';
    // var_dump($time_break1->format('%H時間%i分'));

    // echo '<br /><br />time_break1 = ';
    // var_dump($time_break1);


    if($each_day->break2_in && $each_day->break2_out){
    $time_break2_in = Carbon::createFromFormat('H:i:s', $each_day->break2_in)->format('U');
    $time_break2_out = Carbon::createFromFormat('H:i:s', $each_day->break2_out)->format('U');
    // $time_break2 = $time_break2_out->diffAsCarbonInterval($time_break2_in);
    $time_break2 = $time_break2_out - $time_break2_in;
    }else{
        $time_break2 = NULL;
    }

    // echo '<br /><br />time_break2 = ';
    // var_dump($time_break2->format('%H時間%i分'));
    // echo '<br /><br />time_break2 = ';
    // var_dump($time_break2);

    if($time_break1 && $time_break2){
        $time_break = $time_break1 + $time_break2;
    }elseif($time_break1){
        $time_break = $time_break1;
    }else{
        $time_break=null;
    }


    // echo '<br /><br />time_break = ';
    // var_dump($time_break);//->format('%H:%I:%S')

    // $time_break2_in = Carbon::createFromFormat('H:i:s', $each_day->break2_in);
    // $time_break2_out = Carbon::createFromFormat('H:i:s', $each_day->break2_out);


    // $time_break2 = $time_break2_out->diffAsCarbonInterval($time_break2_in);

    if( $each_day->punch_out!=NULL && $each_day->punch_in < $each_day->punch_out){
        // $time_worked = $time_end->diffAsCarbonInterval($time_start);
        $time_worked = $time_end - $time_start;

    }else{
        $time_worked = NULL;
    }

    

    // echo '<br /><br />time_worked  = ';
    // var_dump($time_worked);

    if($time_break){
        // $time_worked = $time_end->diffAsCarbonInterval($time_start)->subtract($time_break);
       // ->subHours((int)$time_break_array[0])->subMinutes((int)$time_break_array[1]);
        // $time_worked = new Carbon($time_worked->format('U')-$time_break->format('U'));

        // echo '<br /><br />time_worked  = ';
        // var_dump($time_worked);

        // $time_worked = $time_worked->subtract($time_break);
        

        $time_break_formatted = Carbon::parse($time_break)->format('g:i');


        // $time_worked_unix = strtotime($time_worked);
        // $time_break_unix = strtotime($time_break);

        // $time_worked_unix - $time_break_unix;

    }else{
        // $time_break=Carbon::parse('0:0:0');
        // $time_break_formatted=Carbon::parse('00:00:00')->format('g:i');
        // $time_break_formatted = '';
        $time_break_formatted = '0:00';
    }


    // echo '<br /><br />time_worked = ';
    // var_dump($time_worked);//->format('%H:%I:%S')

    // echo '<br /><br />time_worked - time_break = ';
    // var_dump(Carbon::parse($time_worked)->format('H:i:s') );//->format('%H:%I:%S')

    if($time_worked ){
        
        $time_worked = $time_worked - $time_break;
        $time_worked_formatted = Carbon::parse($time_worked)->format('g:i');
    }else{
        $time_worked_formatted = '';
    }


    $monthly_list[$each_day->date] = array_merge( $monthly_list[$each_day->date],[
            'id' => $each_day->id,
            // 'date' => $date_formatted,
            'punch_in' => $this->formatTime($each_day->punch_in),
            'punch_out' => $this->formatTime($each_day->punch_out),
            'break' => $time_break_formatted,
            'time_worked' => $time_worked_formatted,
        ]);

}


// echo '<br /><br />monthly_list = ';
// var_dump($monthly_list);

// echo '<br /><br />path = ';
// var_dump($request->path());
$period_path = $request->path('');

$result=array();
$result['monthly_list'] = $monthly_list;
$result['period'] = $period;
// $result['title'] = $title;
$result['days'] = $days;
$result['period_path'] = $period_path;
// $result['detail_path'] = $detail_path;
$result['month_last'] = $month_last;
$result['month_next'] = $month_next;

return $result;

// return view('list',compact('monthly_list', 'period', 'title', 'days', 'period_path', 'detail_path', 'month_last', 'month_next')); ///'user', 


    }


    //--------------------------------------------------------------------------

    public function dailyRoll( Request $request )//$period,protected 
    {
// echo '<br /><br />';
// echo __FUNCTION__;
// echo '<br /><br />get = ';
// var_dump($_GET);
// echo '<br /><br />post = ';
// var_dump($_POST);


    if(!$date = $request->date){   // = $request->date
        $date_raw = Carbon::now();
        $date = $date_raw->format('Y/m/d');
    }else{
        $date_raw = Carbon::parse($date);
    }

// echo '<br /><br />date = ';
// var_dump($date);

// echo '<br /><br />user_id = ';
// var_dump($user_id);

// echo '<br /><br />user = ';
// var_dump($user);

// $year = $date_raw->format('Y');
// $date = $date_raw->format('m');


// echo '<br /><br />year = ';
// var_dump($year);
// echo '<br /><br />date = ';
// var_dump($date);

$date_last = $date_raw->subDay()->format('Y-m-d');
$date_next = $date_raw->addDay(2)->format('Y-m-d');

// echo '<br /><br />date_last_raw = ';
// var_dump($date_raw->format('Y-m-d'));
// echo '<br /><br />date_last_raw = ';
// var_dump($date_raw->format('Y-m-d'));

// echo '<br /><br />date_last_raw = ';
// var_dump($date_raw->subDay());
// echo '<br /><br />date_next_raw = ';
// var_dump($date_raw->addDay());


// echo '<br /><br />date_last = ';
// var_dump($date_last);
// echo '<br /><br />date_next = ';
// var_dump($date_next);



$list = Timesheet::where('date', 'LIKE', "%{$date}%")->with('User')->get();

// echo '<br /><br />list = ';
// var_dump($list->toArray());
// echo '<br /><br />list count() = ';
// var_dump($list->count());

$daily_list = array();

foreach($list as $each_day){

    // echo '<br /><br />each_day->date = ';
    // var_dump($each_day->date);

    // echo '<br /><br />each_day->User->status = ';
    // var_dump($each_day->User->status);

    if($each_day->User->status===0){
        continue;
    }

    // echo '<br /><br />each_day->break2 = ';
    // var_dump($each_day->break2_out);

    // $time_start = Carbon::createFromFormat('H:i:s', $each_day->punch_in);
    // $time_end = Carbon::createFromFormat('H:i:s', $each_day->punch_out);

    $time_start = Carbon::parse($each_day->date.' '.$each_day->punch_in)->format('U');
    $time_end = Carbon::parse($each_day->date.' '.$each_day->punch_out)->format('U');

    // echo '<br /><br />time_start = ';
    // var_dump($time_start->format('H時間i分'));
    // echo '<br /><br />time_end = ';
    // var_dump($time_end->format('H時間i分'));
    //  echo '<br /><br />time_end = ';
    // var_dump($time_end->format('U'));

    // echo '<br /><br />time_start = ';
    // var_dump($time_start);
    // echo '<br /><br />time_end = ';
    // var_dump($time_end);


    if($each_day->break1_in && $each_day->break1_out){
    $time_break1_in = Carbon::createFromFormat('H:i:s', $each_day->break1_in)->format('U');
    $time_break1_out = Carbon::createFromFormat('H:i:s', $each_day->break1_out)->format('U');
    // $time_break1 = $time_break1_out->diffAsCarbonInterval($time_break1_in);
    $time_break1 = $time_break1_out - $time_break1_in;
    }else{
        $time_break1 = NULL;
    }

    // echo '<br /><br />time_break1 = ';
    // var_dump($time_break1->format('%H時間%i分'));

    // echo '<br /><br />time_break1 = ';
    // var_dump($time_break1);


    if($each_day->break2_in && $each_day->break2_out){
    $time_break2_in = Carbon::createFromFormat('H:i:s', $each_day->break2_in)->format('U');
    $time_break2_out = Carbon::createFromFormat('H:i:s', $each_day->break2_out)->format('U');
    // $time_break2 = $time_break2_out->diffAsCarbonInterval($time_break2_in);
    $time_break2 = $time_break2_out - $time_break2_in;
    }else{
        $time_break2 = NULL;
    }

    // echo '<br /><br />time_break2 = ';
    // var_dump($time_break2->format('%H時間%i分'));
    // echo '<br /><br />time_break2 = ';
    // var_dump($time_break2);

    if($time_break1 && $time_break2){
        $time_break = $time_break1 + $time_break2;
    }elseif($time_break1){
        $time_break = $time_break1;
    }else{
        $time_break=null;
    }


    // echo '<br /><br />time_break = ';
    // var_dump($time_break);//->format('%H:%I:%S')

    // $time_break2_in = Carbon::createFromFormat('H:i:s', $each_day->break2_in);
    // $time_break2_out = Carbon::createFromFormat('H:i:s', $each_day->break2_out);


    // $time_break2 = $time_break2_out->diffAsCarbonInterval($time_break2_in);

    if( $each_day->punch_out!=NULL && $each_day->punch_in < $each_day->punch_out){
        // $time_worked = $time_end->diffAsCarbonInterval($time_start);
        $time_worked = $time_end - $time_start;

    }else{
        $time_worked = NULL;
    }


    // echo '<br /><br />time_worked  = ';
    // var_dump($time_worked);

    if($time_break){
        // $time_worked = $time_end->diffAsCarbonInterval($time_start)->subtract($time_break);
       // ->subHours((int)$time_break_array[0])->subMinutes((int)$time_break_array[1]);
        // $time_worked = new Carbon($time_worked->format('U')-$time_break->format('U'));

        // echo '<br /><br />time_worked  = ';
        // var_dump($time_worked);

        // $time_worked = $time_worked->subtract($time_break);

        $time_break_formatted = Carbon::parse($time_break)->format('g:i');


        // $time_worked_unix = strtotime($time_worked);
        // $time_break_unix = strtotime($time_break);

        // $time_worked_unix - $time_break_unix;

    }else{
        // $time_break=Carbon::parse('0:0:0');
        // $time_break_formatted=Carbon::parse('00:00:00')->format('g:i');
        // $time_break_formatted = '';
        $time_break_formatted = '0:00';
    }


    // echo '<br /><br />time_worked = ';
    // var_dump($time_worked);//->format('%H:%I:%S')

    // echo '<br /><br />time_worked - time_break = ';
    // var_dump(Carbon::parse($time_worked)->format('H:i:s') );//->format('%H:%I:%S')

    if($time_worked ){
        $time_worked = $time_worked - $time_break;
        $time_worked_formatted = Carbon::parse($time_worked)->format('g:i');
    }else{
        $time_worked_formatted = '';
    }


    $daily_list[$each_day->id] = [
            'id' => $each_day->id,
            'name' => $each_day->user->name,
            // 'date' => $date_formatted,
            'punch_in' => $this->formatTime($each_day->punch_in),
            'punch_out' => $this->formatTime($each_day->punch_out),
            'break' => $time_break_formatted,
            'time_worked' => $time_worked_formatted,
        ];

}


// echo '<br /><br />daily_list = ';
// var_dump($daily_list);

// echo '<br /><br />path = ';
// var_dump($request->path());

$date_path = $request->path('');

$result=array();
$result['daily_list'] = $daily_list;
$result['date'] = $date;
// $result['title'] = $title;
$result['date_path'] = $date_path;
// $result['detail_path'] = $detail_path;
$result['date_last'] = $date_last;
$result['date_next'] = $date_next;

return $result;

// return view('list',compact('daily_list', 'date', 'title', 'days', 'date_path', 'detail_path', 'date_last', 'date_next')); ///'user', 


    }

    //-----------------------------------------------------------------------------------

    protected function formatTime($time){
        if($time){
            $result = substr($time, 0, 5);
        }else{
            $result = '';
        }
        return $result;
    }

//----------------------------------------------------------

    public function detail($id, Request $request)
    {
// echo __FUNCTION__;
// echo '<br /><br />get = ';
// var_dump($_GET);
// echo '<br /><br />post = ';
// var_dump($_POST);

$user = Auth::user();
//$request->path('');
if(preg_match("/^admin/i",$request->path())){
    if($user->status > 1){
        $admin = TRUE;
        $title = '勤怠詳細';
    }else{
        // return redirect('/logout');
        echo '<br /><br />logout ';
    }
}else{
    $admin = FALSE;
    $title = '勤怠詳細';
}


$list = Timesheet::find($id);

if($admin){
    $user = User::find($list->user_id);
}


echo '<br /><br />user->name = ';
var_dump($user->name);

$date_exp = explode('-', $list->date);

// echo '<br /><br />date_exp = ';
// var_dump($date_exp);
$sheet = array();

$sheet['year'] = $date_exp[0];
$sheet['date'] = $date_exp[1].'/'.$date_exp[2];

$sheet['punch_in'] = $this->formatTime($list->punch_in);
$sheet['punch_out'] = $this->formatTime($list->punch_out);

$sheet['break1_in'] = $this->formatTime($list->break1_in);
$sheet['break1_out'] = $this->formatTime($list->break1_out);

$sheet['break2_in'] = $this->formatTime($list->break2_in);
$sheet['break2_out'] = $this->formatTime($list->break2_out);

$sheet['status'] = $this->formatTime($list->status);

// $year = Carbon::parse($list->date)->isoFormat('YYYY');
// $date = Carbon::parse($list->date)->isoFormat('MM/DD');

// $result = substr($time, 0, 5);

// 'punch_in' => $this->formatTime($list->punch_in),

if($admin){

// echo '<br /><br />request -> path() = ';
// var_dump($request->path());

    if(preg_match("/^admin\/requests/i",$request->path()) ){
        // return view('detail_suspend',compact('user', 'list', 'sheet', 'admin' ,'title'));
        $suspend = '_suspend';
    }else{  //preg_match("/^admin\/attendances/i",$request->path())
        // return view('detail',compact('user', 'list', 'sheet', 'admin'));
        $suspend = '';
    }


}elseif($list->status === 1 ){
    // return view('detail_suspend',compact('user', 'list', 'sheet', 'admin'));
    $suspend = '_suspend';
}else{
    // return view('detail', compact('user', 'list', 'sheet', 'admin'));
    $suspend = '';
}

return view('detail'.$suspend, compact('user', 'list', 'sheet', 'admin' ,'title'));

    }

    //--------------------------------------------------

    //(UpdateTimesheetRequest $request, Timesheet $timesheet)

    public function update(Request $request)
    {
// echo __FUNCTION__;
// echo '<br /><br />get = ';
// var_dump($_GET);
// echo '<br /><br />post = ';
// var_dump($_POST);

$user = Auth::user();

if($user->status > 1 && preg_match("/^admin/i",$request->path())){
    $new_status= 2 ;
}else{
    $new_status= 1 ;
}

// $punch_in = $request->punch_in.':00';
// $punch_out = $request->punch_out.':00';
// $break1_in = $request->break1_in.':00';
// $break1_out = $request->break1_out.':00';
// $break2_in = $request->break2_in.':00';
// $break2_out = $request->break2_out.':00';
// $remark = $request->remark;
// $status = '1';
// $update_at = now()->format('Y-m-d H:i:s');

// echo '<br /><br />Auth:id user_id = ';
// var_dump($user_id);
// echo '<br /><br />today = ';
// var_dump($today);
// echo '<br /><br />time = ';
// var_dump($time);
// echo '<br /><br />punch = ';
// var_dump($punch);

$sheet_today = Timesheet::find($request->id);

    $sheet_today->punch_in = $request->punch_in.':00';
    $sheet_today->punch_out = $request->punch_out.':00';
    $sheet_today->break1_in = $request->break1_in.':00';
    $sheet_today->break1_out = $request->break1_out.':00';
    $sheet_today->break2_in = $request->break2_in.':00';
    $sheet_today->break2_out = $request->break2_out.':00';
    $sheet_today->remark = $request->remark;
    $sheet_today->status = $new_status;

    if( $sheet_today->isDirty() ){ $sheet_today->save(); }
    // $sheet_today->save();
    // $sheet_today->update();

if(preg_match("/^admin/i",$request->path())){
    return redirect('/admin/users/'.$sheet_today->user_id.'/attendances?period='.substr($sheet_today->date, 0, 7));
    ///admin/users/{user_id}/attendances
}else{
    return redirect('/attendance/list?period='.substr($sheet_today->date, 0, 7));
}


    }

//--------------------------------------------------------------

    public function approve($id, Request $request)
    {
//         echo __FUNCTION__;
// echo '<br /><br />get = ';
// var_dump($_GET);
// echo '<br /><br />post = ';
// var_dump($_POST);

if($request->status == 2){ return redirect('/admin/requests?status=2');}

$user = Auth::user();

if($user->status > 1 && preg_match("/^admin/i",$request->path())){
    $new_status= 2 ;
}else{
    $new_status= 1 ;
}

$sheet_today = Timesheet::find($request->id);

    $sheet_today->status = $new_status;

    if( $sheet_today->isDirty() ){ $sheet_today->save(); }

// echo '<br /><br />request->getPathInfo() = ';
// var_dump($request->getPathInfo());

// exit;
    return redirect('/admin/requests');
    ///admin/users/{user_id}/attendances

    }

    public function requestRoll( Request $request )//$user_id, 
    {

// echo '<br /><br />';
// echo __FUNCTION__;
// echo '<br /><br />get = ';
// var_dump($_GET);
// echo '<br /><br />post = ';
// var_dump($_POST);

    // if(!$user_id){
    //     $user_id = Auth::id();
    //     $title = '勤怠一覧';
    //     $detail_path = '/attendance/detail/';
    // }else{
    //     $user = user::find($user_id);
    //     $title = $user->name.'さんの勤怠';
    //     $detail_path = '/admin/attendances/';
    // }
$title = '申請一覧';

$user = Auth::user();
if($user->status <= 1 && preg_match("/^admin/i",$request->path())){
    // return redirect('/logout');
    echo '<br /><br />logout ';
}

// echo '<br /><br />user_id = ';
// var_dump($user_id);
// echo '<br /><br />user = ';
// var_dump($user);

if($request->status == '2'){
    $status = 2 ;
    $bold_1 = '';
    $bold_2 = ' bold';
}else{
    $status = 1 ;
    $bold_1 = ' bold';
    $bold_2 = '';
}

if($user->status > 1 && preg_match("/^admin/i",$request->path())){
    // return redirect('/attendance/list');

// echo '<br /><br />true  ';

    $list = Timesheet::where('status','=', $status)->with('User')->get();

}else{

// echo '<br /><br />else  ';

    $list = Timesheet::where('user_id','=', $user->id)->where('status','=', $status)->with('User')->get();

}

// echo '<br /><br />list = ';
// var_dump($list->toArray());

// echo '<br /><br />list count() = ';
// var_dump($list->count());


// $format_source = 'Y-m-d';
// $format_period = 'm/d()';

// $monthly_list = array();
// for($i = 1; $i <= $days; $i++){

// // $day = sprintf('%02d', $i);

// $day=Carbon::create($year, $month, $i);

// $date_key = $day->isoFormat('YYYY-MM-DD');
// $date_formatted = $day->isoFormat('MM/DD(ddd)'); //->formatLocalized('m月j(D)');

// // echo '<br /><br />date_key = ';
// // var_dump($date_key);
// // echo '<br /><br />date_formatted = ';
// // var_dump($date_formatted);
// // var_dump(now()->formatLocalized('%m/%d(%a)'));
// // var_dump(now()->isoFormat('MM/DD(ddd)'));

// // echo '<br /><br />list->day = ';
// // var_dump($list->$day);

//     $monthly_list[$date_key] = [
//         'id' => '',
//         'date' => $date_formatted,
//         'punch_in' => '',
//         'punch_out' => '',
//         'break' => '',
//         'time_worked' => '',
//     ];
// }
// echo '<br /><br />monthly_list = ';
// var_dump($monthly_list);

$request_list = array();

foreach($list as $each_request){

    // echo '<br /><br />each_request->date = ';
    // var_dump($each_request->date);

    // // echo '<br /><br />time_break1 = ';
    // // var_dump($time_break1);

    // // echo '<br /><br />time_break2 = ';
    // // var_dump($time_break2->format('%H時間%i分'));
    // echo '<br /><br />time_break2 = ';
    // var_dump($time_break2);

    // echo '<br /><br />time_break = ';
    // var_dump($time_break);//->format('%H:%I:%S')

    // $time_break2_in = Carbon::createFromFormat('H:i:s', $each_day->break2_in);
    // $time_break2_out = Carbon::createFromFormat('H:i:s', $each_day->break2_out);


    // $time_break2 = $time_break2_out->diffAsCarbonInterval($time_break2_in);

    // echo '<br /><br />time_worked  = ';
    // var_dump($time_worked);

    // echo '<br /><br />time_worked = ';
    // var_dump($time_worked);//->format('%H:%I:%S')

    // echo '<br /><br />time_worked - time_break = ';
    // var_dump(Carbon::parse($time_worked)->format('H:i:s') );//->format('%H:%I:%S')

if($each_request->status === 1 ){
    $status = '承認待ち';
}elseif($each_request->status === 2 ){
    $status = '承認済み';
}else{
    $status = '';
}

// $time_worked_formatted = Carbon::parse($time_worked)->format('g:i');

    $request_list[$each_request->id] = [
            'id' => $each_request->id,
            'status' => $status,
            'name' => $each_request->user['name'],
            'date' => Carbon::parse($each_request->date)->format('Y/m/d'),
            'remark' => $each_request->remark,
            'updated_at' => Carbon::parse($each_request->update_at)->format('Y/m/d'),
        ];

}


// echo '<br /><br />monthly_list = ';
// var_dump($monthly_list);

// echo '<br /><br />path = ';
// var_dump($request->path());
// $period_path = $request->path('');

// $result=array();
// $result['monthly_list'] = $monthly_list;
// $result['period'] = $period;
// $result['title'] = $title;
// $result['days'] = $days;
// $result['period_path'] = $period_path;
// $result['detail_path'] = $detail_path;
// $result['month_last'] = $month_last;
// $result['month_next'] = $month_next;

// return $result;

// return view('list',compact('monthly_list', 'period', 'title', 'days', 'period_path', 'detail_path', 'month_last', 'month_next')); ///'user',
// $request_list = $list;

return view('request',compact('request_list', 'title', 'bold_1', 'bold_2'));


    }


//-----------------------------------------------------------

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTimesheetRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTimesheetRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Timesheet  $timesheet
     * @return \Illuminate\Http\Response
     */
    public function show(Timesheet $timesheet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Timesheet  $timesheet
     * @return \Illuminate\Http\Response
     */
    public function edit(Timesheet $timesheet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTimesheetRequest  $request
     * @param  \App\Models\Timesheet  $timesheet
     * @return \Illuminate\Http\Response
     */
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Timesheet  $timesheet
     * @return \Illuminate\Http\Response
     */
    public function destroy(Timesheet $timesheet)
    {
        //
    }
}
