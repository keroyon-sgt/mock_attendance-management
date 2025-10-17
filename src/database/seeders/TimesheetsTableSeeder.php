<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Timesheet;

use DateTimeImmutable;

class TimesheetsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $today = new DateTimeImmutable('now');
        $date = new DateTimeImmutable('-3 month');
        $m_day = $date->format('d');
        $date = $date->modify(-$m_day.' day');

        // $user_count='';//for test
        $user_count = User::count();
        if(!$user_count){$user_count = 7 ;}//for test

        $work_style = [
            0 =>[
                'punch_in'=>'09:00',
                'punch_out'=>'20:30',
                'break1_in'=>'12:00',
                'break1_out'=>'13:00',
                'break2_in'=>'18:00',
                'break2_out'=>'18:30',
            ],

            1 =>[
                'punch_in'=>'09:00',
                'punch_out'=>'18:00',
                'break1_in'=>'12:00',
                'break1_out'=>'13:00',
                'break2_in'=>NULL,
                'break2_out'=>NULL,
            ],
        ];

        $note = [
            '0'=>'',
            '1'=>'電車遅延のため',
            '2'=>'',
            '3'=>'遅延のため',
    ];

        while($date <= $today)
        {
            if($date->format('w') > 0 && $date->format('w') < 6){

                for($val = 1; $val <= $user_count; $val++){

                    $rand = rand(0,3);

                    if($rand === 0){
                        $style = 0;
                    }else{
                        $style = 1;
                    }


                    $param = [
                        'user_id' => $val,
                        // 'year' => $date->format('Y'),
                        // 'month' => $date->format('m'),
                        // 'day' => $date->format('d'),
                        'date' => $date->format('Y-m-d'),
                        'punch_in' => $work_style[$style]['punch_in'],
                        'punch_out' => $work_style[$style]['punch_out'],
                        'break1_in' => $work_style[$style]['break1_in'],
                        'break1_out' => $work_style[$style]['break1_out'],
                        'break2_in' => $work_style[$style]['break2_in'],
                        'break2_out' => $work_style[$style]['break2_out'],
                        'remark' => $note[$rand],
                    ];

                    // DB::table('timesheets')->insert($param);
                    Timesheet::create($param);
                }
            }

            $date = $date->modify('+1 day');

        }
    }
}
