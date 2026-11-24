<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Администратор',
            'email' => 'admin@adflow.ru',
            'phone' => '+79990000000',
            'password' => 'admin123',
            'role' => 'admin',
        ]);

        Campaign::create([
            'name' => 'Весенняя распродажа',
            'platform_name' => 'Яндекс.Директ',
            'status' => 'active',
            'budget_total' => 86900,
            'budget_spent' => 78200,
            'revenue_attributed' => 243984,
            'end_date' => '2026-11-30',
        ]);

        Campaign::create([
            'name' => 'Открытие точки',
            'platform_name' => 'ВКонтакте',
            'status' => 'active',
            'budget_total' => 100000,
            'budget_spent' => 61400,
            'revenue_attributed' => 169464,
            'end_date' => '2026-12-12',
        ]);

        Campaign::create([
            'name' => 'Акция «2 по цене 1»',
            'platform_name' => 'ВКонтакте',
            'status' => 'paused',
            'budget_total' => 50000,
            'budget_spent' => 24700,
            'revenue_attributed' => 47918,
            'end_date' => '2026-11-20',
        ]);
    }
}