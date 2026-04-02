<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Default',
                'email' => 'admin@local.test',
                'role' => 'admin',
            ],
            [
                'name' => 'Editor Default',
                'email' => 'editor@local.test',
                'role' => 'editor',
            ],
            [
                'name' => 'Wartawan Sample',
                'email' => 'wartawan@local.test',
                'role' => 'wartawan',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password'),
                    'role' => $user['role'],
                    'is_active' => true,
                ]
            );
        }
    }
}
