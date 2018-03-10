<?php

use App\Domain\User;
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
        Schema::disableForeignKeyConstraints();
        DB::table('users')->truncate();
        Schema::enableForeignKeyConstraints();

        User::create([
            'name'      => 'Evan Hsu',
            'email'     => 'evanhsu@gmail.com',
            'password'  => bcrypt('password'),
        ]);

        factory(User::class, 2)->make();
    }
}
