<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Symfony\Component\HttpFoundation\StreamedResponse;

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

        if (!Auth::check()) { return redirect('/login'); }

        $user = Auth::user();

        $today = now()->format('Y-m-d');

        $clock=now()->format('H:i');

        $sheet_today = Timesheet::where('user_id', $user->id)->where('date', $today)->latest()->orderBy('id', 'DESC')->first();

        if($sheet_today){
            if($sheet_today->date === $today){

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

        }else{
            $situation = '0';
            $situation_text = '勤務外';
        }

        $today =  Carbon::parse($today)->isoFormat('YYYY年M月D日(ddd)'); //->formatLocalized('m月j(D)');
        $title = '出勤登録';

        return view('attendance',compact('situation', 'situation_text', 'today', 'clock', 'title'));
    }

    public function punch(Request $request)
    {

        $user_id = Auth::id();
        $now = now();
        $today = $now->format('Y-m-d');
        $time = $now->format('H:i:s');
        $punch = $request->punch;

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

        return view('list',compact('monthly_list', 'period', 'title', 'days', 'period_path', 'detail_path', 'month_last', 'month_next'));
    }

    public function adminAttendanceRoll( $user_id, Request $request )//$period,
    {
        $admin = Auth::user();
        if($admin->status <= 1 && preg_match("/^admin/i",$request->path())){
            return redirect('/logout');
        }

        $admin = TRUE;

        $user = user::find($user_id);
        $title = $user->name.'さんの勤怠';
        $detail_path = '/admin/attendances/';

        $result = $this -> monthlyRoll( $user_id, $request );

        $monthly_list = $result['monthly_list'];
        $period = $result['period'];
        $days = $result['days'];
        $period_path = $result['period_path'];
        $month_last = $result['month_last'];
        $month_next = $result['month_next'];

        return view('list',compact('monthly_list', 'period', 'title', 'days', 'period_path', 'detail_path', 'month_last', 'month_next', 'admin', 'user_id')); ///'user', 
    }

    public function dailyAttendanceRoll( Request $request )//$period,
    {
        $user = Auth::user();
        if($user->status <= 1 && preg_match("/^admin/i",$request->path())){
            return redirect('/logout');
        }

        if($request->date){
            $date_formatted = str_replace('-', '/', $request->date);
        }else{
            $date_formatted = now()->format('Y/m/d');
        }

        $title = $date_formatted.'の勤怠';
        $detail_path = '/admin/attendances/';

        $result = $this -> dailyRoll( $request );

        $daily_list = $result['daily_list'];
        $date = $result['date'];
        $date_path = $result['date_path'];
        $date_last = $result['date_last'];
        $date_next = $result['date_next'];

        return view('list_daily',compact('daily_list', 'date', 'title', 'date_path', 'detail_path', 'date_last', 'date_next'));

    }

    public function monthlyRoll( $user_id, Request $request )//$period,protected
    {

        if(!$period = $request->period){   // = $request->period
            $period_raw = Carbon::now();
            $period = $period_raw->format('Y-m');
        }else{
            $period_raw = Carbon::parse($period);
        }

        $year = $period_raw->format('Y');
        $month = $period_raw->format('m');

        $month_last = $period_raw->subMonth()->format('Y-m');
        $month_next = $period_raw->addMonth(2)->format('Y-m');

        $days = Carbon::create($year, $month, 1)->daysInMonth;

        $list = Timesheet::where('user_id', $user_id)->where('date', 'LIKE', "%{$period}%")->get();

        $format_source = 'Y-m-d';
        $format_period = 'm/d()';

        $monthly_list = array();
        for($i = 1; $i <= $days; $i++){

            $day=Carbon::create($year, $month, $i);

            $date_key = $day->isoFormat('YYYY-MM-DD');
            $date_formatted = $day->isoFormat('MM/DD(ddd)');

            $monthly_list[$date_key] = [
                'id' => '',
                'date' => $date_formatted,
                'punch_in' => '',
                'punch_out' => '',
                'break' => '',
                'time_worked' => '',
            ];
        }

        foreach($list as $each_day){

            $time_start = Carbon::parse($each_day->date.' '.$each_day->punch_in)->format('U');
            $time_end = Carbon::parse($each_day->date.' '.$each_day->punch_out)->format('U');

            if($each_day->break1_in && $each_day->break1_out){
                $time_break1_in = Carbon::createFromFormat('H:i:s', $each_day->break1_in)->format('U');
                $time_break1_out = Carbon::createFromFormat('H:i:s', $each_day->break1_out)->format('U');
                $time_break1 = $time_break1_out - $time_break1_in;
            }else{
                $time_break1 = NULL;
            }

            if($each_day->break2_in && $each_day->break2_out){
                $time_break2_in = Carbon::createFromFormat('H:i:s', $each_day->break2_in)->format('U');
                $time_break2_out = Carbon::createFromFormat('H:i:s', $each_day->break2_out)->format('U');
                $time_break2 = $time_break2_out - $time_break2_in;
            }else{
                $time_break2 = NULL;
            }

            if($time_break1 && $time_break2){
                $time_break = $time_break1 + $time_break2;
            }elseif($time_break1){
                $time_break = $time_break1;
            }else{
                $time_break=null;
            }

            if( $each_day->punch_out!=NULL && $each_day->punch_in < $each_day->punch_out){
                $time_worked = $time_end - $time_start;
            }else{
                $time_worked = NULL;
            }

            if($time_break){
                $time_break_formatted = Carbon::parse($time_break)->format('g:i');
            }else{
                $time_break_formatted = '0:00';
            }

            if($time_worked ){
                $time_worked = $time_worked - $time_break;
                $time_worked_formatted = Carbon::parse($time_worked)->format('g:i');
            }else{
                $time_worked_formatted = '';
            }

            $monthly_list[$each_day->date] = array_merge( $monthly_list[$each_day->date],[
                    'id' => $each_day->id,
                    'punch_in' => $this->formatTime($each_day->punch_in),
                    'punch_out' => $this->formatTime($each_day->punch_out),
                    'break' => $time_break_formatted,
                    'time_worked' => $time_worked_formatted,
                ]);

        }

        $period_path = $request->path('');

        $result=array();
        $result['monthly_list'] = $monthly_list;
        $result['period'] = $period;
        $result['days'] = $days;
        $result['period_path'] = $period_path;
        $result['month_last'] = $month_last;
        $result['month_next'] = $month_next;

        return $result;
    }

    public function dailyRoll( Request $request )//$period,protected 
    {

        if(!$date = $request->date){
            $date_raw = Carbon::now();
            $date = $date_raw->format('Y/m/d');
        }else{
            $date_raw = Carbon::parse($date);
        }

        $date_last = $date_raw->subDay()->format('Y-m-d');
        $date_next = $date_raw->addDay(2)->format('Y-m-d');

        $list = Timesheet::where('date', 'LIKE', "%{$date}%")->with('User')->get();

        $daily_list = array();

        foreach($list as $each_day){

            if($each_day->User->status===0){
                continue;
            }

            $time_start = Carbon::parse($each_day->date.' '.$each_day->punch_in)->format('U');
            $time_end = Carbon::parse($each_day->date.' '.$each_day->punch_out)->format('U');

            if($each_day->break1_in && $each_day->break1_out){
                $time_break1_in = Carbon::createFromFormat('H:i:s', $each_day->break1_in)->format('U');
                $time_break1_out = Carbon::createFromFormat('H:i:s', $each_day->break1_out)->format('U');
                $time_break1 = $time_break1_out - $time_break1_in;
            }else{
                $time_break1 = NULL;
            }

            if($each_day->break2_in && $each_day->break2_out){
                $time_break2_in = Carbon::createFromFormat('H:i:s', $each_day->break2_in)->format('U');
                $time_break2_out = Carbon::createFromFormat('H:i:s', $each_day->break2_out)->format('U');
                $time_break2 = $time_break2_out - $time_break2_in;
            }else{
                $time_break2 = NULL;
            }

            if($time_break1 && $time_break2){
                $time_break = $time_break1 + $time_break2;
            }elseif($time_break1){
                $time_break = $time_break1;
            }else{
                $time_break=null;
            }

            if( $each_day->punch_out!=NULL && $each_day->punch_in < $each_day->punch_out){
                $time_worked = $time_end - $time_start;
            }else{
                $time_worked = NULL;
            }

            if($time_break){
                $time_break_formatted = Carbon::parse($time_break)->format('g:i');
            }else{
                $time_break_formatted = '0:00';
            }

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

        $date_path = $request->path('');

        $result=array();
        $result['daily_list'] = $daily_list;
        $result['date'] = $date;
        $result['date_path'] = $date_path;
        $result['date_last'] = $date_last;
        $result['date_next'] = $date_next;

        return $result;

    }

    protected function formatTime($time){

        if($time){
            $result = substr($time, 0, 5);
        }else{
            $result = '';
        }
        return $result;
    }

    public function detail($id, Request $request)
    {

        $user = Auth::user();

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

        $date_exp = explode('-', $list->date);

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

        if($admin){

            if(preg_match("/^admin\/requests/i",$request->path()) ){
                $suspend = '_suspend';
            }else{
                $suspend = '';
            }

        }elseif($list->status === 1 ){
            $suspend = '_suspend';
        }else{
            $suspend = '';
        }

        return view('detail'.$suspend, compact('user', 'list', 'sheet', 'admin' ,'title'));
    }

    public function update(Request $request)
    {

        $user = Auth::user();

        if($user->status > 1 && preg_match("/^admin/i",$request->path())){
            $new_status= 2 ;
        }else{
            $new_status= 1 ;
        }

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

        if(preg_match("/^admin/i",$request->path())){
            return redirect('/admin/users/'.$sheet_today->user_id.'/attendances?period='.substr($sheet_today->date, 0, 7));

        }else{
            return redirect('/attendance/list?period='.substr($sheet_today->date, 0, 7));
        }
    }
    public function approve($id, Request $request)
    {

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

        return redirect('/admin/requests');

    }

    public function requestRoll( Request $request )//$user_id, 
    {

        $title = '申請一覧';

        $user = Auth::user();
        if($user->status <= 1 && preg_match("/^admin/i",$request->path())){
            return redirect('/logout');
        }

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
            $list = Timesheet::where('status','=', $status)->with('User')->get();
        }else{
            $list = Timesheet::where('user_id','=', $user->id)->where('status','=', $status)->with('User')->get();
        }

        $request_list = array();

        foreach($list as $each_request){

            if($each_request->status === 1 ){
                $status = '承認待ち';
            }elseif($each_request->status === 2 ){
                $status = '承認済み';
            }else{
                $status = '';
            }

            $request_list[$each_request->id] = [
                    'id' => $each_request->id,
                    'status' => $status,
                    'name' => $each_request->user['name'],
                    'date' => Carbon::parse($each_request->date)->format('Y/m/d'),
                    'remark' => $each_request->remark,
                    'updated_at' => Carbon::parse($each_request->update_at)->format('Y/m/d'),
                ];
        }

        return view('request',compact('request_list', 'title', 'bold_1', 'bold_2'));
    }

    public function export($user_id, Request $request)
    {

        $admin = Auth::user();
        if($admin->status <= 1 && preg_match("/^admin/i",$request->path())){
            return redirect('/logout');
        }

        $user = user::find($user_id);
        $file_name = $user->name.'_'.$request->period;

        $result = $this -> monthlyRoll( $user_id, $request );

        $monthly_list = $result['monthly_list'];
        // $period = $result['period'];
        // $days = $result['days'];
        // $period_path = $result['period_path'];
        // $month_last = $result['month_last'];
        // $month_next = $result['month_next'];

        $csvHeader = [
            '日付',
            '出勤',
            '退勤',
            '休憩',
            '合計',
        ];


        $csvData=array();
        foreach($monthly_list as $date => $each_sheet){

// echo '<br /><br />each_sheet = ';
// var_dump($each_sheet);

            $csvData[ $date ]= array(
                $each_sheet['date'],
                $each_sheet['punch_in'],
                $each_sheet['punch_out'],
                $each_sheet['break'],
                $each_sheet['time_worked'],
            );

        }

// echo '<br /><br />csvData = ';
// var_dump($csvData);

// echo '<br /><br />file_name = ';
// var_dump($file_name);
// exit;

        $response = new StreamedResponse(function () use ($csvHeader, $csvData) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $csvHeader);

            foreach ($csvData as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$file_name.'.csv"',
        ]);

        return $response;
    }

}
