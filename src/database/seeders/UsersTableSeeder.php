<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'name' => '管理者ユーザ',
            'email' => 'admin@example.test',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'status' => '2',
        ];
        User::create($param);

        $param = [
            'name' => '一般ユーザ1',
            'email' => 'user1@example.test',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'status' => '1',
        ];
        User::create($param);

        $param = [
            'name' => '一般ユーザ2',
            'email' => 'user2@example.test',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'status' => '1',
        ];
        User::create($param);

        $param = [
            'name' => '一般ユーザ3',
            'email' => 'user3@example.test',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'status' => '1',
        ];
        User::create($param);

        $param = [
            'name' => '一般ユーザ4',
            'email' => 'user4@example.test',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'status' => '1',
        ];
        User::create($param);

        $param = [
            'name' => '一般ユーザ5',
            'email' => 'user5@example.test',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'status' => '1',
        ];
        User::create($param);

        $param = [
            'name' => '一般ユーザ6',
            'email' => 'user6@example.test',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'status' => '1',
        ];
        User::create($param);

        $param = [
            'name' => '一般ユーザ7',
            'email' => 'user7@example.test',
            'email_verified_at' => Carbon::now(),
            'password' => '',
            'status' => '1',
        ];
        User::create($param);

        $param = [
            'name' => '除籍ユーザ1',
            'email' => 'expired1@example.test',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'status' => '0',
        ];
        User::create($param);
    }
}
