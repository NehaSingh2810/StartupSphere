<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;
    public function run(): void
    {
        User::updateOrCreate(['email' => '123@gmail.com'], [
            'name' => 'Nehaa',
            'password' => Hash::make('1234567890'),
        ]);

        User::updateOrCreate(['email' => 'demo@startupsphere.com'], [
            'name' => 'Demo User',
            'password' => Hash::make('password'),
        ]);

        User::updateOrCreate(['email' => 'investor@startupsphere.com'], [
            'name' => 'Startup Investor Demo',
            'password' => Hash::make('password'),
        ]);
    }
}
