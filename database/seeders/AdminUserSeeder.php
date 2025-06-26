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
            'cp_no' => '0935-688-5819',
            'password' => bcrypt('konceptoadmin'),
        ]);

        User::create([
            'first_name' => 'Marc Renzi',
            'last_name' => 'Angeles',
            'email' => 'iznerangeles@gmail.com',
            'role' => 'school_admin',
            'cp_no' => '0921-270-4695',
            'password' => bcrypt('user123'),
        ]);
    }
}
