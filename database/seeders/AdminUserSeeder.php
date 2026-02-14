<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@mscs.org.sa'],
            [
                'name' => 'مدير النظام',
                'password' => \Hash::make('12345678'),
                'role' => 'super_admin',
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'manager@mscs.org.sa'],
            [
                'name' => 'مدير المنصة',
                'password' => \Hash::make('12345678'),
                'role' => 'manager',
            ]
        );
    }
}
