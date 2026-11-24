<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignTask;
use App\Models\LeadRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Тестовые пользователи (после проверки пароли лучше сменить)
        User::updateOrCreate(['email' => 'admin@adflow.ru'], [
            'name' => 'Администратор',
            'phone' => '+79990000000',
            'password' => 'admin123',
            'role' => 'admin',
        ]);

        User::updateOrCreate(['email' => 'manager@adflow.ru'], [
            'name' => 'Анна Кузнецова',
            'phone' => '+79990000001',
            'password' => 'manager123',
            'role' => 'manager',
        ]);

        $executor = User::updateOrCreate(['email' => 'executor@adflow.ru'], [
            'name' => 'Дмитрий Волков',
            'phone' => '+79990000002',
            'password' => 'executor123',
            'role' => 'executor',
        ]);

        $spring = Campaign::firstOrCreate(['name' => 'Весенняя распродажа'], [
            'platform_name' => 'Яндекс.Директ',
            'status' => 'active',
            'budget_total' => 86900,
            'budget_spent' => 78200,
            'revenue_attributed' => 243984,
            'end_date' => '2026-11-30',
        ]);

        $opening = Campaign::firstOrCreate(['name' => 'Открытие точки'], [
            'platform_name' => 'ВКонтакте',
            'status' => 'active',
            'budget_total' => 100000,
            'budget_spent' => 61400,
            'revenue_attributed' => 169464,
            'end_date' => '2026-12-12',
        ]);

        Campaign::firstOrCreate(['name' => 'Акция «2 по цене 1»'], [
            'platform_name' => 'ВКонтакте',
            'status' => 'paused',
            'budget_total' => 50000,
            'budget_spent' => 24700,
            'revenue_attributed' => 47918,
            'end_date' => '2026-11-20',
        ]);

        CampaignTask::firstOrCreate(['title' => 'Текст объявлений для Директа'], [
            'campaign_id' => $spring->id,
            'user_id' => $executor->id,
            'assignee_role' => 'copywriter',
            'due_date' => now()->addDay()->toDateString(),
        ]);

        CampaignTask::firstOrCreate(['title' => 'Баннеры для ВК, 3 размера'], [
            'campaign_id' => $opening->id,
            'assignee_role' => 'designer',
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        CampaignTask::firstOrCreate(['title' => 'Согласовать бюджет «Открытие точки»'], [
            'campaign_id' => $opening->id,
            'assignee_role' => 'manager',
            'due_date' => now()->addDays(5)->toDateString(),
        ]);

        LeadRequest::firstOrCreate(['client_name' => 'Кофейня «Утро»'], [
            'contact' => '+7 999 111-22-33',
            'platform_name' => 'ВКонтакте',
            'budget_estimate' => 40000,
        ]);

        LeadRequest::firstOrCreate(['client_name' => 'Студия маникюра'], [
            'contact' => 'studio@example.ru',
            'platform_name' => 'Яндекс.Директ',
            'budget_estimate' => 60000,
        ]);
    }
}
