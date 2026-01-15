<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Illuminate\Support\MessageBag;
// use Illuminate\Http\RedirectResponse;

use Symfony\Component\HttpFoundation\StreamedResponse;

use App\Http\Requests\StoreTimesheetRequest;
use App\Http\Requests\UpdateTimesheetRequest;

use App\Models\Timesheet;
use App\Models\BreakTimesheet;
use App\Models\Correction;
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

        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        if ($user->status > 1) {
            $admin_mode = TRUE;
        } else {
            $admin_mode = FALSE;
        }

        $today = now()->format('Y-m-d');

        $clock = now()->format('H:i');

        $sheet_today = Timesheet::where('user_id', $user->id)->where('date', $today)->latest()->orderBy('id', 'DESC')->first();

        if ($sheet_today) {
            if ($sheet_today->date === $today) {

                if ($sheet_today->punch_out) {
                    $situation = '3';
                    $situation_text = '退勤済';
                } else {

                    $sheet_break = BreakTimesheet::where('user_id', $user->id)->where('date', $today)->latest()->orderBy('id', 'DESC')->first();

                    if ($sheet_break !== NULL && $sheet_break->break_in) {
                        if ($sheet_break->break_out) {
                            $situation = '1';
                            $situation_text = '勤務中';
                            // }
                        } else {
                            $situation = '2';
                            $situation_text = '休憩中';
                        }
                    } else {
                        $situation = '1';
                        $situation_text = '勤務中';
                    }
                }
            }
        } else {
            $situation = '0';
            $situation_text = '勤務外';
        }

        $today =  Carbon::parse($today)->isoFormat('YYYY年M月D日(ddd)'); //->formatLocalized('m月j(D)');
        $title = '出勤登録';

        return view('attendance', compact('situation', 'situation_text', 'today', 'clock', 'title', 'admin_mode'));
    }

    public function punch(Request $request)
    {

        echo '<br /><br />request->punch = ';
        var_dump($request->punch);


        $user_id = Auth::id();
        $now = now();
        $today = $now->format('Y-m-d');
        $time = $now->format('H:i:s');
        $punch = $request->punch;

        if ($request->punch === 'punch_in') {
            Timesheet::create([
                'user_id' => $user_id,
                'date' => $today,
                'punch_in' => $time,
            ]);
        } else {

            if ($request->punch === 'break_in') {

                echo '<br /><br />here! ';

                $sheet_today = Timesheet::where('user_id', $user_id)->where('date', $today)->latest()->orderBy('id', 'DESC')->first();


                BreakTimesheet::create([
                    'user_id' => $user_id,
                    'timesheet_id' => $sheet_today->id,
                    'date' => $today,
                    'break_in' => $time,
                ]);
            } else {

                if ($request->punch === 'break_out') {
                    $sheet = BreakTimesheet::where('user_id', $user_id)->where('date', $today)->latest()->orderBy('id', 'DESC')->first();
                } elseif ($request->punch === 'punch_out') {
                    $sheet = Timesheet::where('user_id', $user_id)->where('date', $today)->latest()->orderBy('id', 'DESC')->first();
                }

                if ($sheet && $request->punch) {
                    $sheet->$punch = $time;       //punch → punch selector → $selector に変更
                    if ($sheet->isDirty()) {
                        $sheet->save();
                    }
                }
            }
        }

        return redirect('/');
    }

    public function attendanceRoll(Request $request) //$period,
    {
        $user_id = Auth::id();
        $title = '勤怠一覧';
        $detail_path = '/attendance/detail/';
        $admin_mode = FALSE;

        $result = $this->monthlyRoll($user_id, $request);

        $monthly_list = $result['monthly_list'];
        $period = $result['period'];
        $days = $result['days'];
        $period_path = $result['period_path'];
        $month_last = $result['month_last'];
        $month_next = $result['month_next'];

        return view('list', compact('monthly_list', 'period', 'title', 'days', 'period_path', 'detail_path', 'month_last', 'month_next', 'admin_mode'));
    }

    public function adminAttendanceRoll($user_id, Request $request) //$period,
    {

        $admin_mode = FALSE;
        $admin = Auth::user();
        if (preg_match("/^admin/i", $request->path())) {
            if ($admin->status > 1) {
                $admin_mode = TRUE;
            } else {
                return redirect('/logout');
            }
        }

        $admin_mode = TRUE;

        $user = user::find($user_id);
        $title = $user->name . 'さんの勤怠';
        $detail_path = '/admin/attendances/';

        $result = $this->monthlyRoll($user_id, $request);

        $monthly_list = $result['monthly_list'];
        $period = $result['period'];
        $days = $result['days'];
        $period_path = $result['period_path'];
        $month_last = $result['month_last'];
        $month_next = $result['month_next'];

        return view('list', compact('monthly_list', 'period', 'title', 'days', 'period_path', 'detail_path', 'month_last', 'month_next', 'admin_mode', 'user_id')); ///'user', 
    }

    public function dailyAttendanceRoll(Request $request) //$period,
    {

        $admin_mode = FALSE;

        $user = Auth::user();
        if (preg_match("/^admin/i", $request->path())) {
            if ($user->status > 1) {
                $admin_mode = TRUE;
            } else {
                return redirect('/logout');
            }
        }

        if ($request->date) {
            $date_formatted = str_replace('-', '/', $request->date);
        } else {
            $date_formatted = now()->format('Y/m/d');
        }

        $title = $date_formatted . 'の勤怠';
        $detail_path = '/admin/attendances/';

        $result = $this->dailyRoll($request);

        $daily_list = $result['daily_list'];
        $date = $result['date'];
        $date_path = $result['date_path'];
        $date_last = $result['date_last'];
        $date_next = $result['date_next'];

        return view('list_daily', compact('daily_list', 'date', 'title', 'date_path', 'detail_path', 'date_last', 'date_next', 'admin_mode'));
    }

    protected function monthlyRoll($user_id, Request $request) //$period,protected
    {

        if (!$period = $request->period) {   // = $request->period
            $period_raw = Carbon::now();
            $period = $period_raw->format('Y-m');
        } else {
            $period_raw = Carbon::parse($period);
        }


        $year = $period_raw->format('Y');
        $month = $period_raw->format('m');

        $month_last = $period_raw->subMonth()->format('Y-m');
        $month_next = $period_raw->addMonth(2)->format('Y-m');

        $days = Carbon::create($year, $month, 1)->daysInMonth;

        $list = Timesheet::where('user_id', $user_id)->where('date', 'LIKE', "%{$period}%")->get();

        $monthly_list = array();
        for ($i = 1; $i <= $days; $i++) {

            $day = Carbon::create($year, $month, $i);

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

        foreach ($list as $each_day) {

            $corrections = Correction::where('user_id', $user_id)->where('timesheet_id', $each_day->id)->get();
            //->latest()->orderBy('id', 'ASC')

            $corrections = $corrections->sortBy('id');

            if ($corrections->isNotEmpty()) {

                foreach ($corrections as $each_correction) {

                    if (!isset($each_correction->break_timesheet_id)) {

                        $each_day->punch_in = $each_correction->in;
                        $each_day->punch_out = $each_correction->out;
                    }
                }
            }

            $time_start = Carbon::parse($each_day->date . ' ' . $each_day->punch_in)->format('U');
            $time_end = Carbon::parse($each_day->date . ' ' . $each_day->punch_out)->format('U');

            $breaks = BreakTimesheet::where('user_id', $user_id)->where('date', $each_day->date)->get();

            $time_break = NULL;

            foreach ($breaks as $each_break) {

                if ($corrections->isNotEmpty()) {

                    foreach ($corrections as $each_correction) {

                        if ($each_break->id === $each_correction->break_timesheet_id) {

                            $each_break->break_in = $each_correction->in;
                            $each_break->break_out = $each_correction->out;

                            // echo '<br /><br />each_break->break_in = ';
                            // var_dump($each_break->break_in);
                            // echo '<br /><br />each_break->break_out = ';
                            // var_dump($each_break->break_out);
                        }
                    }
                }



                if ($each_break->break_in && $each_break->break_out) {

                    // echo '<br /><br />each_break->break_in = ';
                    // var_dump($each_break->break_in);
                    // echo '<br /><br />each_break->break_out = ';
                    // var_dump($each_break->break_out);

                    $time_break_in = Carbon::createFromFormat('H:i:s', $each_break->break_in)->format('U');
                    $time_break_out = Carbon::createFromFormat('H:i:s', $each_break->break_out)->format('U');

                    $time_break += $time_break_out - $time_break_in;

                    // echo '<br /><br />time_break_in = ';
                    // var_dump($time_break_in);
                    // echo '<br /><br />time_break_out = ';
                    // var_dump($time_break_out);
                    // echo '<br /><br />time_break = ';
                    // var_dump($time_break);
                }
            }


            if ($each_day->punch_out != NULL && $each_day->punch_in < $each_day->punch_out) {
                $time_worked = $time_end - $time_start;
            } else {
                $time_worked = NULL;
            }

            // echo '<br /><br />time_worked = ';
            // var_dump($time_worked);

            if ($time_break) {
                $time_break_formatted = Carbon::parse($time_break)->format('G:i');
            } else {
                $time_break_formatted = '0:00';
            }

            if ($time_worked) {
                $time_worked = $time_worked - $time_break;
                $time_worked_formatted = Carbon::parse($time_worked)->format('G:i');
            } else {
                $time_worked_formatted = '';
            }

            $monthly_list[$each_day->date] = array_merge($monthly_list[$each_day->date], [
                'id' => $each_day->id,
                'punch_in' => $this->formatTime($each_day->punch_in),
                'punch_out' => $this->formatTime($each_day->punch_out),
                'break' => $time_break_formatted,
                'time_worked' => $time_worked_formatted,
            ]);
        }

        $period_path = $request->path('');

        $result = array();
        $result['monthly_list'] = $monthly_list;
        $result['period'] = $period;
        $result['days'] = $days;
        $result['period_path'] = $period_path;
        $result['month_last'] = $month_last;
        $result['month_next'] = $month_next;

        return $result;
    }

    protected function dailyRoll(Request $request) //$period
    {

        if (!$date = $request->date) {
            $date_raw = Carbon::now();
            $date = $date_raw->format('Y/m/d');
        } else {
            $date_raw = Carbon::parse($date);
        }

        $date_last = $date_raw->subDay()->format('Y-m-d');
        $date_next = $date_raw->addDay(2)->format('Y-m-d');

        $list = Timesheet::where('date', 'LIKE', "%{$date}%")->with('User')->get();

        $daily_list = array();

        foreach ($list as $each_day) {

            if ($each_day->User->status === 0) {
                continue;
            }

            $time_start = Carbon::parse($each_day->date . ' ' . $each_day->punch_in)->format('U');
            $time_end = Carbon::parse($each_day->date . ' ' . $each_day->punch_out)->format('U');

            //----------------

            $breaks = BreakTimesheet::where('user_id', $each_day->user_id)->where('date', $each_day->date)->get();

            $time_break = NULL;

            foreach ($breaks as $each_break) {
                if ($each_break->break_in && $each_break->break_out) {

                    // echo '<br /><br />each_break->break_in = ';
                    // var_dump($each_break->break_in);
                    // echo '<br /><br />each_break->break_out = ';
                    // var_dump($each_break->break_out);

                    $time_break_in = Carbon::createFromFormat('H:i:s', $each_break->break_in)->format('U');
                    $time_break_out = Carbon::createFromFormat('H:i:s', $each_break->break_out)->format('U');

                    $time_break += $time_break_out - $time_break_in;

                    // echo '<br /><br />time_break_in = ';
                    // var_dump($time_break_in);
                    // echo '<br /><br />time_break_out = ';
                    // var_dump($time_break_out);
                    // echo '<br /><br />time_break = ';
                    // var_dump($time_break);
                }
            }




            // if ($each_day->break1_in && $each_day->break1_out) {
            //     $time_break1_in = Carbon::createFromFormat('H:i:s', $each_day->break1_in)->format('U');
            //     $time_break1_out = Carbon::createFromFormat('H:i:s', $each_day->break1_out)->format('U');
            //     $time_break1 = $time_break1_out - $time_break1_in;
            // } else {
            //     $time_break1 = NULL;
            // }

            // if ($each_day->break2_in && $each_day->break2_out) {
            //     $time_break2_in = Carbon::createFromFormat('H:i:s', $each_day->break2_in)->format('U');
            //     $time_break2_out = Carbon::createFromFormat('H:i:s', $each_day->break2_out)->format('U');
            //     $time_break2 = $time_break2_out - $time_break2_in;
            // } else {
            //     $time_break2 = NULL;
            // }

            // if ($time_break1 && $time_break2) {
            //     $time_break = $time_break1 + $time_break2;
            // } elseif ($time_break1) {
            //     $time_break = $time_break1;
            // } else {
            //     $time_break = null;
            // }

            if ($each_day->punch_out != NULL && $each_day->punch_in < $each_day->punch_out) {
                $time_worked = $time_end - $time_start;
            } else {
                $time_worked = NULL;
            }

            if ($time_break) {
                $time_break_formatted = Carbon::parse($time_break)->format('g:i');
            } else {
                $time_break_formatted = '0:00';
            }

            if ($time_worked) {
                $time_worked = $time_worked - $time_break;
                $time_worked_formatted = Carbon::parse($time_worked)->format('g:i');
            } else {
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

        $result = array();
        $result['daily_list'] = $daily_list;
        $result['date'] = $date;
        $result['date_path'] = $date_path;
        $result['date_last'] = $date_last;
        $result['date_next'] = $date_next;

        return $result;
    }

    protected function formatTime($time)
    {

        if ($time) {
            $result = substr($time, 0, 5);
        } else {
            $result = '';
        }
        return $result;
    }

    public function detail($id, Request $request)
    {
        // echo '<br /><br />request = ';
        // var_dump($request->toArray());

        // echo '<br /><br />request->punch_in = ';
        // var_dump($request->punch_in);


        $admin_mode = FALSE;
        $title = '勤怠詳細';

        // $user = Auth::user();
        // if (preg_match("/^admin/i", $request->path())) {
        //     if ($user->status > 1) {
        //         $admin_mode = TRUE;
        //         $title = '勤怠詳細';
        //     } else {
        //         // return redirect('/logout');
        //     }
        // }


        $list = Timesheet::find($id);


        if (Auth::guard('admins')->check()) {
            $admin_mode = TRUE;
            $title = '勤怠詳細';

            $user = User::find($list->user_id);
        } elseif ($user = Auth::user()) {
            if ($user->id !== $list->user_id) {
                return back();
            }
        } else {
            return back();
        }

        $corrections = Correction::where('user_id', $user->id)->where('timesheet_id', $list->id)->where('break_timesheet_id', NULL)->latest()->orderBy('id', 'DESC')->first();  //->get()


        if ($corrections) {
            $list->punch_in = $corrections->in;
            $list->punch_out = $corrections->out;
        }


        // if ($admin_mode) {
        //     $user = User::find($list->user_id);
        // }

        $date_exp = explode('-', $list->date);

        $sheet = array();

        $sheet['year'] = $date_exp[0];
        $sheet['date'] = $date_exp[1] . '/' . $date_exp[2];

        $sheet['punch_in'] = $this->formatTime($list->punch_in);
        $sheet['punch_out'] = $this->formatTime($list->punch_out);

        $sheet['breaks'] = array();

        // $breaks = BreakTimesheet::where('user_id', $user->id)->where('date', $list->date)->get();
        // foreach ($breaks as $each_break) {
        //     // if ($each_break->break_in && $each_break->break_out) {

        //     // echo '<br /><br />each_break->break_in = ';
        //     // var_dump($each_break->break_in);
        //     // echo '<br /><br />each_break->break_out = ';
        //     // var_dump($each_break->break_out);

        //     // }

        //     $sheet['breaks'][$each_break->id]['in'] =  $this->formatTime($each_break->break_in);
        //     $sheet['breaks'][$each_break->id]['out'] =  $this->formatTime($each_break->break_out);
        // }

        $sheet['breaks'] = $this->getBreaks($user->id, $id);

        // echo '<br /><br />sheet[breaks] = ';
        // var_dump($sheet['breaks']);
        // echo '<br /><br />ksort(sheet[breaks] = ';
        // var_dump(ksort($sheet['breaks']));

        // $sheet['break1_in'] = $this->formatTime($list->break1_in);
        // $sheet['break1_out'] = $this->formatTime($list->break1_out);

        // $sheet['break2_in'] = $this->formatTime($list->break2_in);
        // $sheet['break2_out'] = $this->formatTime($list->break2_out);

        $sheet['status'] = $this->formatTime($list->status);

        if ($admin_mode) {

            if (preg_match("/^admin\/requests/i", $request->path())) {
                $suspend = '_suspend';
            } else {
                $suspend = '';
            }
        } elseif ($list->status === 1) {
            $suspend = '_suspend';
        } else {
            $suspend = '';
        }

        return view('detail' . $suspend, compact('user', 'list', 'sheet', 'admin_mode', 'title'));
    }

    public function update(Request $request)
    {
        // echo '<br /><br />request = ';
        // var_dump($request->toArray());

        // echo '<br /><br />request->punch_in = ';
        // var_dump($request->punch_in);
        // echo '<br /><br />strtotime(request->punch_in = ';
        // var_dump(strtotime($request->punch_in));

        // $user = Auth::user();

        // if ($user->status > 1 && preg_match("/^admin/i", $request->path())) {
        //     $new_status = 2;
        // } else {
        //     $new_status = 1;
        // }


        if (Auth::guard('admins')->check()) {
            $user = user::find($request->user_id);
            $new_status = 2;
        } elseif ($user = Auth::user()) {
            $new_status = 1;
        }

        // echo '<br /><br />Auth::user() = ';
        // var_dump(Auth::user());

        // echo '<br /><br />Auth::guard(admins)->check() = ';
        // var_dump(Auth::guard('admins')->user());

        // echo '<br /><br />new_status = ';
        // var_dump($new_status);

        $breaks_current = $this->getBreaks($user->id, $request->id);

        // echo '<br /><br />breaks_current = ';
        // var_dump($breaks_current);



        $errors = new MessageBag();
        $error_count = 0;

        if (strtotime($request->punch_in) > strtotime($request->punch_out)) {
            $errors->add('punch', '出勤時間が退社時間より遅くなっています。');
            $error_count++;
        }

        foreach ($request->break as $break_id => $each_break) {

            if ($break_id === 'new') {

                if (!($each_break['in'] && $each_break['out']) && !(empty($each_break['in']) && empty($each_break['out']))) {
                    $errors->add('break_' . $break_id, '休憩開始時間と終了時間は両方必要です。');
                    $error_count++;
                    continue;
                }
            }

            if (strtotime($each_break['in']) > strtotime($each_break['out'])) {
                $errors->add('break_' . $break_id, '休憩開始時間が休憩終了時間より遅くなっています。');
                $error_count++;
                continue;
            }
            if (strtotime($each_break['in']) > strtotime($request->punch_out)) {
                $errors->add('break_' . $break_id, '休憩開始時間が終業時間より遅くなっています。');
                $error_count++;
                continue;
            }
            if (strtotime($each_break['out']) > strtotime($request->punch_out)) {
                $errors->add('break_' . $break_id, '休憩終了時間が終業時間より遅くなっています。');
                $error_count++;
                continue;
            }

            foreach ($request->break as $ather_break_id => $each_ather_break) {

                if ($break_id === $ather_break_id) {
                    // echo '<br /><br />id continue';
                    continue;
                }

                // echo '<br /><br />ather_break_id  = ';
                // var_dump($ather_break_id);

                // echo '<br /><br />each_ather_break[in]  = ';
                // var_dump($each_break['in']);
                // echo '<br /><br />each_ather_break[out]  = ';
                // var_dump($each_break['out']);


                // if (isset($breaks_current[$ather_break_id])) {
                //     echo '<br /><br />breaks_current[ather_break_id]  = ';
                //     var_dump($breaks_current[$ather_break_id]);
                // }

                // echo '<br /><br />each_ather_break[in]  = ';
                // var_dump($each_ather_break['in']);
                // echo '<br /><br />each_ather_break[out]  = ';
                // var_dump($each_ather_break['out']);

                if (empty($each_ather_break['in']) || empty($each_ather_break['out'])) {
                    // echo '<br /><br />empty continue';
                    continue;
                }

                if (
                    isset($breaks_current[$ather_break_id])
                    && $breaks_current[$ather_break_id]['in'] === $each_ather_break['in']
                    && $breaks_current[$ather_break_id]['out'] === $each_ather_break['out']
                ) {
                    // echo '<br /><br />continue';
                    continue;
                }

                if (
                    strtotime($each_ather_break['in']) <= strtotime($each_break['in']) &&
                    strtotime($each_break['in']) <= strtotime($each_ather_break['out'])
                ) {
                    $errors->add('break_' . $ather_break_id, '休憩開始時間が他の休憩時間と重なっています。');
                    // $errors->add('break_' . $break_id, '休憩開始時間が他の休憩時間と重なっています。1');
                    $error_count++;
                    // echo '<br /><br />error1 continue';
                    continue;
                }
                if (
                    strtotime($each_ather_break['in']) <= strtotime($each_break['out']) &&
                    strtotime($each_break['out']) <= strtotime($each_ather_break['out'])
                ) {
                    $errors->add('break_' . $ather_break_id, '休憩終了時間が他の休憩時間と重なっています。');
                    // $errors->add('break_' . $break_id, '休憩終了時間が他の休憩時間と重なっています。');
                    $error_count++;
                    // echo '<br /><br />error2 continue';
                    continue;
                }

                if (
                    strtotime($each_break['in']) <= strtotime($each_ather_break['in']) &&
                    strtotime($each_ather_break['in']) <= strtotime($each_break['out'])
                ) {
                    $errors->add('break_' . $ather_break_id, '休憩開始時間が他の休憩時間と重なっています。');
                    // $errors->add('break_' . $break_id, '休憩開始時間が他の休憩時間と重なっています。');
                    $error_count++;
                    // echo '<br /><br />error1 continue';
                    continue;
                }
                if (
                    strtotime($each_break['in']) <= strtotime($each_ather_break['out']) &&
                    strtotime($each_ather_break['out']) <= strtotime($each_break['out'])
                ) {
                    $errors->add('break_' . $ather_break_id, '休憩終了時間が他の休憩時間と重なっています。');
                    // $errors->add('break_' . $break_id, '休憩終了時間が他の休憩時間と重なっています。');
                    $error_count++;
                    // echo '<br /><br />error2 continue';
                    continue;
                }
            }
        }

        // echo '<br /><br />sheet_today->punch_in 2= ';
        // var_dump(substr($sheet_today->punch_in, 0, 5));
        // echo '<br /><br />errors  = ';
        // var_dump($errors);

        // exit;

        if ($error_count !== 0) {
            return back()->withErrors($errors)->withInput();
        }


        // exit;

        $sheet_today = Timesheet::find($request->id);

        if (
            substr($sheet_today->punch_in, 0, 5) !== $request->punch_in ||
            substr($sheet_today->punch_out, 0, 5) !== $request->punch_out
        ) {

            if (substr($sheet_today->punch_in, 0, 5) !== $request->punch_in) {
                $new_punch_in = $request->punch_in . ':00';
            } else {
                $new_punch_in = $sheet_today->punch_in;
            }

            if (substr($sheet_today->punch_out, 0, 5) !== $request->punch_out) {
                $new_punch_out = $request->punch_out . ':00';
            } else {
                $new_punch_out = $sheet_today->punch_out;
            }

            Correction::create([
                'user_id' => $user->id,
                'timesheet_id' => $sheet_today->id,
                'break_timesheet_id' => NULL,
                // 'date' => $sheet_today->date,
                'in' => $new_punch_in,
                'out' => $new_punch_out,
            ]);
        }

        // echo '<br /><br />sheet_today->punch_in 2= ';
        // var_dump(substr($sheet_today->punch_in, 0, 5));
        // echo '<br /><br />request->punch_in  = ';
        // var_dump($request->punch_in);
        // exit;

        // echo '<br /><br />request->break = ';
        // var_dump($request->break);

        // if (isset($request->break)) {
        //     echo '<br /><br />request->break = ';
        //     var_dump($request->break);
        // }
        // exit;


        foreach ($request->break as $break_id => $each_break) {

            if ($break_id === 'new') {

                if ($each_break['in'] && $each_break['out']) {

                    BreakTimesheet::create([
                        'user_id' => $user->id,
                        'timesheet_id' => $sheet_today->id,
                        'date' => $sheet_today->date,
                        'break_in' => $each_break['in'] . ':00',
                        'break_out' => $each_break['out'] . ':00',
                    ]);
                }
            } else {

                // echo '<br /><br />break_id = ';
                // var_dump($break_id);
                // echo '<br /><br />each_break[in] = ';
                // var_dump($each_break['in']);

                if (
                    !($breaks_current[$break_id]['in'] === $each_break['in']
                        && $breaks_current[$break_id]['out'] === $each_break['out'])
                )
                    Correction::create([
                        'user_id' => $user->id,
                        'timesheet_id' => $sheet_today->id,
                        'break_timesheet_id' => $break_id,
                        // 'date' => $sheet_today->date,
                        'in' => $each_break['in'] . ':00',
                        'out' => $each_break['out'] . ':00',
                    ]);
            }
        }


        $sheet_today->remark = $request->remark;
        $sheet_today->status = $new_status;

        if ($sheet_today->isDirty()) {
            $sheet_today->save();
        }

        if (preg_match("/^admin/i", $request->path())) {
            return redirect('/admin/users/' . $sheet_today->user_id . '/attendances?period=' . substr($sheet_today->date, 0, 7));
        } else {
            return redirect('/attendance/list?period=' . substr($sheet_today->date, 0, 7));
        }
    }


    protected function getBreaks($user_id, $timesheet_id) //$period,protected
    {
        $breaks = BreakTimesheet::where('user_id', $user_id)->where('timesheet_id', $timesheet_id)->get();
        //latest()->orderBy('break_in', 'ASC')->

        $breaks = $breaks->sortBy('break_in');

        $sheet = array();

        foreach ($breaks as $each_break) {

            // if ($each_break->break_in && $each_break->break_out) {

            // echo '<br /><br />each_break->id = ';
            // var_dump($each_break->id);
            // echo '<br /><br />each_break->break_in = ';
            // var_dump($each_break->break_in);
            // echo '<br /><br />each_break->break_out = ';
            // var_dump($each_break->break_out);
            // }

            $sheet[$each_break->id]['in'] =  $this->formatTime($each_break->break_in);
            $sheet[$each_break->id]['out'] =  $this->formatTime($each_break->break_out);
        }

        $corrections = Correction::where('user_id', $user_id)->where('timesheet_id', $timesheet_id)->get();
        //->latest()->orderBy('id', 'ASC')

        $corrections = $corrections->sortBy('id');

        foreach ($corrections as $each_correction) {

            // if ($each_correction->in && $each_correction->out) {

            //     echo '<br /><br />each_correction->break_in = ';
            //     var_dump($each_correction->in);
            //     echo '<br /><br />each_correction->break_out = ';
            //     var_dump($each_correction->out);


            //     echo '<br /><br />each_correction->id = ';
            //     var_dump($each_correction->break_timesheet_id);

            //     echo '<br /><br />sheet[each_correction->break_timesheet_id][in] = ';
            //     var_dump($sheet[$each_correction->break_timesheet_id]['in']);
            //     echo '<br /><br />sheet[each_correction->break_timesheet_id][out] = ';
            //     var_dump($sheet[$each_correction->break_timesheet_id]['out']);
            // }

            if ($each_correction->break_timesheet_id) {
                $sheet[$each_correction->break_timesheet_id]['in'] =  $this->formatTime($each_correction->in);
                $sheet[$each_correction->break_timesheet_id]['out'] =  $this->formatTime($each_correction->out);
            }
        }

        return $sheet;
    }


    public function approve($id, Request $request)
    {

        if ($request->status == 2) {
            return redirect('/admin/requests?status=2');
        }

        $user = Auth::user();

        if ($user->status > 1 && preg_match("/^admin/i", $request->path())) {
            $new_status = 2;
        } else {
            $new_status = 1;
        }

        $sheet_today = Timesheet::find($request->id);

        $sheet_today->status = $new_status;

        if ($sheet_today->isDirty()) {
            $sheet_today->save();
        }

        return redirect('/admin/requests');
    }

    public function requestRoll(Request $request) //$user_id, 
    {

        $title = '申請一覧';

        $admin_mode = FALSE;
        $detail_path = '/attendance/detail/';

        $user = Auth::user();
        if (preg_match("/^admin/i", $request->path())) {
            if ($user->status > 1) {
                $admin_mode = TRUE;
                $detail_path = '/admin/requests/';
            } else {
                return redirect('/logout');
            }
        }


        if ($request->status == '2') {
            $status = 2;
            $bold_1 = '';
            $bold_2 = ' bold';
        } else {
            $status = 1;
            $bold_1 = ' bold';
            $bold_2 = '';
        }

        if ($user->status > 1 && preg_match("/^admin/i", $request->path())) {
            $list = Timesheet::where('status', '=', $status)->with('User')->get();
        } else {
            $list = Timesheet::where('user_id', '=', $user->id)->where('status', '=', $status)->with('User')->get();
        }

        $request_list = array();

        foreach ($list as $each_request) {

            if ($each_request->status === 1) {
                $status = '承認待ち';
            } elseif ($each_request->status === 2) {
                $status = '承認済み';
            } else {
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

        return view('request', compact('request_list', 'title', 'bold_1', 'bold_2', 'admin_mode', 'detail_path'));
    }

    public function export($user_id, Request $request)
    {

        $admin = Auth::user();
        if ($admin->status <= 1 && preg_match("/^admin/i", $request->path())) {
            return redirect('/logout');
        }

        $user = user::find($user_id);
        $file_name = $user->name . '_' . $request->period;

        $result = $this->monthlyRoll($user_id, $request);

        $monthly_list = $result['monthly_list'];

        $csvHeader = [
            '日付',
            '出勤',
            '退勤',
            '休憩',
            '合計',
        ];


        $csvData = array();
        foreach ($monthly_list as $date => $each_sheet) {

            $csvData[$date] = array(
                $each_sheet['date'],
                $each_sheet['punch_in'],
                $each_sheet['punch_out'],
                $each_sheet['break'],
                $each_sheet['time_worked'],
            );
        }

        $response = new StreamedResponse(function () use ($csvHeader, $csvData) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $csvHeader);

            foreach ($csvData as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $file_name . '.csv"',
        ]);

        return $response;
    }
}
