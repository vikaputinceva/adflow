<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // создаём администратора
        User::create([
            'name' => 'Администратор',
            'email' => 'admin@adflow.ru',
            'phone' => '+79990000000',
            'password' => 'admin123', // пароль захэшируется сам
            'role' => 'admin',
        ]);
    }
}