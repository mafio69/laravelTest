<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'FOO_mafio',
            'email' => 'foo@gmail.com',
            'password' => bcrypt('foo-bar-baz'),
        ]);
        DB::table('users')->insert([
            'name' => 'BAR_mafio',
            'email' => 'bar@gmail.com',
            'password' => bcrypt('foo-bar-baz'),
        ]);
        DB::table('users')->insert([
            'name' => 'BAZ_mafio',
            'email' => 'baz@gmail.com',
            'password' => bcrypt('foo-bar-baz'),
        ]);
    }
}
