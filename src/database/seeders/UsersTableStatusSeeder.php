<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\User;

class UsersTableStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users_data = User::all();
        if ($users_data) {
            foreach($users_data as $data){
                $data->status = '1';
                $data->save();
            }
        }

        $data = User::find(1);
        if ($data) {
            $data->status = '2';
            $data->save();
        }

        $data = User::find(8);
        if ($data) {
            $data->status = '0';
            $data->save();
        }
    }
}
