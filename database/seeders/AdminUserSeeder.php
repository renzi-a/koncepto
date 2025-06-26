<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

use function Laravel\Prompts\table;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'first_name' => 'Reena Ophelia',
            'last_name' => 'Angeles',
            'email' => 'reenaopheliaangeles5@gmail.com',
            'role' => 'admin',
            'password' => bcrypt('konceptoadmin'),
        ]);

        User::create([
            'first_name' => 'Marc Renzi',
            'last_name' => 'Angeles',
            'email' => 'iznerangeles@gmail.com',
            'role' => 'user',
            'password' => bcrypt('user123'),
        ]);
    }
}
