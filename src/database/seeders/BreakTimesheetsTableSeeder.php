<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\BreakTimesheet;

use DateTimeImmutable;

class BreakTimesheetsTableSeeder extends Seeder
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
        $date = $date->modify(-$m_day . ' day');

        // $user_count='';//for test
        $user_count = User::count();
        if (!$user_count) {
            $user_count = 7;
        } //for test

        $work_style = [
            0 => [

                'break01_in' => '12:00',
                'break01_out' => '12:45',
                'break02_in' => '15:00',
                'break02_out' => '15:05',
                'break03_in' => '17:00',
                'break03_out' => '17:05',
            ],

            1 => [
                'break01_in' => '12:00',
                'break01_out' => '12:45',
                'break02_in' => '15:00',
                'break02_out' => '15:15',
            ],
        ];

        $ten = 0;

        while ($date <= $today) {
            if ($date->format('w') > 0 && $date->format('w') < 6) {

                for ($val = 1; $val <= $user_count; $val++) {

                    $rand = rand(0, 3);

                    if ($rand === 0) {
                        $style = 0;
                    } else {
                        $style = 1;
                    }


                    $param = [
                        'user_id' => $val,
                        'timesheet_id' => $val + $ten,
                        'date' => $date->format('Y-m-d'),

                        'break_in' => $work_style[$style]['break01_in'],
                        'break_out' => $work_style[$style]['break01_out'],
                    ];

                    // DB::table('timesheets')->insert($param);
                    BreakTimesheet::create($param);

                    $param = [
                        'user_id' => $val,
                        'timesheet_id' => $val + $ten,
                        'date' => $date->format('Y-m-d'),

                        'break_in' => $work_style[$style]['break02_in'],
                        'break_out' => $work_style[$style]['break02_out'],
                    ];

                    // DB::table('timesheets')->insert($param);
                    BreakTimesheet::create($param);

                    if (isset($work_style[$style]['break03_in'])) {
                        $param = [
                            'user_id' => $val,
                            'timesheet_id' => $val + $ten,
                            'date' => $date->format('Y-m-d'),

                            'break_in' => $work_style[$style]['break03_in'],
                            'break_out' => $work_style[$style]['break03_out'],
                        ];

                        // DB::table('timesheets')->insert($param);
                        BreakTimesheet::create($param);
                    }
                }
                $ten += $user_count;
            }

            $date = $date->modify('+1 day');
        }
    }
}
