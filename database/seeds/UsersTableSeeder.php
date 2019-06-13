<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        DB::table('users')->delete();
        $users = array(
            ['name' => 'Andrei Wahyu', 'email' => 'andreiwahyu@gmail.com', 'password' => Hash::make('password'), 'api_token' => Str::random(40)],
        );
        foreach ($users as $user) {
            User::create($user);
        }
        Model::reguard();
    }
}
