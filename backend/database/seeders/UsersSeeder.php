<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('users')->truncate();
        Schema::enableForeignKeyConstraints();

        User::create([
            'name' => 'Alexandru Popescu',
            'email' => 'alexandru@popescu.vip',
            'password' => 'Ap!7#xK9@mQz2$vL',
            'profile_image' => env('APP_URL').'/storage/admin.jpg'
        ])->assignRole('admin');
    }
}
