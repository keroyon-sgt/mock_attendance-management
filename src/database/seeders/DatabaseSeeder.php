<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(8)->create();
        // $this->call(UsersTableStatusSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(TimesheetsTableSeeder::class);
        
    }
}
