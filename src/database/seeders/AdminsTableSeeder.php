<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Admin::truncate();

        $admins_data = [];
        for ($i = 1; $i <= 5; $i++) {
            $admins_data[] = [
                'name' => sprintf('admin_%02d', $i),
                'email' => sprintf('admin_%02d@example.com', $i),
                'email_verified_at' => Carbon::now(),
                'password' => sprintf('00000000', $i),
                'status' => '2',

            ];
        }

        foreach ($admins_data as $data) {
            $admin = new Admin();
            $admin->name = $data['name'];
            $admin->email = $data['email'];
            $admin->email_verified_at = $data['email_verified_at'];
            $admin->password = Hash::make($data['password']);
            $admin->status = $data['status'];
            $admin->save();
        }
    }
}
