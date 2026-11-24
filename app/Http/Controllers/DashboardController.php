<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignTask;
use App\Models\LeadRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->get('search', ''));
        $selectedRole = $request->get('role', 'admin');

        $roleLabels = [
            'admin' => 'Администратор',
            'manager' => 'Менеджер',
            'executor' => 'Исполнитель',
        ];

        $user = auth()->user();
        if (!$user && Schema::hasTable('users')) {
            $user = User::orderBy('id', 'desc')->first();
        }

        $displayUserName = $user ? $user->name : 'Алексей С.';
        $displayUserRole = $roleLabels[$selectedRole] ?? 'Администратор';

        $words = explode(' ', trim($displayUserName));
        $firstLetter = mb_substr($words[0] ?? 'А', 0, 1);
        $secondLetter = mb_substr($words[1] ?? ($words[0] ?? 'С'), 0, 1);
        $displayUserInitials = mb_strtoupper($firstLetter . $secondLetter);

        $allCampaigns = Campaign::orderBy('budget_spent', 'desc')->get();

        if ($allCampaigns->isEmpty()) {
            $campaigns = collect([
                (object) ['id' => 1, 'name' => 'Весенняя распродажа', 'budget_spent' => 78200],
                (object) ['id' => 2, 'name' => 'Открытие точки', 'budget_spent' => 61400],
                (object) ['id' => 3, 'name' => 'Ретаргетинг клиентов', 'budget_spent' => 42900],
                (object) ['id' => 4, 'name' => 'Акция «2 по цене 1»', 'budget_spent' => 24700],
                (object) ['id' => 5, 'name' => 'Конкурс в сообществе', 'budget_spent' => 11200],
            ]);
            $totalCampaignsCount = 18;
            $activeCampaignsCount = 12;
            $totalBudget = 320000;
            $totalSpent = 218400;
            $totalRevenue = 620000;
            $roi = 184;
        } else {
            if ($search !== '') {
                $campaigns = $allCampaigns->filter(function ($item) use ($search) {
                    return stripos($item->name, $search) !== false;
                });
            } else {
                $campaigns = $allCampaigns;
            }

            $totalCampaignsCount = $allCampaigns->count();
            $activeCampaignsCount = $allCampaigns->where('status', 'active')->count();
            $totalBudget = $allCampaigns->sum('budget_total');
            $totalSpent = $allCampaigns->sum('budget_spent');
            $totalRevenue = $allCampaigns->sum('revenue_attributed');
            $roi = $totalSpent > 0 ? round((($totalRevenue - $totalSpent) / $totalSpent) * 100) : 0;
        }

        $budgetUsedPercent = $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100) : 0;
        $budgetLeftPercent = max(0, 100 - $budgetUsedPercent);

        $newLeadsCount = (Schema::hasTable('lead_requests') && LeadRequest::count() > 0)
            ? LeadRequest::where('status', 'new')->count()
            : 47;

        if (Schema::hasTable('campaign_tasks') && CampaignTask::count() > 0) {
            $openTasksCount = CampaignTask::where('is_done', false)->count();
            $tasks = CampaignTask::orderBy('id', 'desc')->take(4)->get()->map(function ($task) {
                return (object) [
                    'title' => $task->title,
                    'role_label' => $task->assignee_role === 'manager' ? 'Менеджер' : 'Исполнитель',
                    'due_label' => $task->due_date ? date('d.m.Y', strtotime($task->due_date)) : 'Сегодня',
                    'is_done' => $task->is_done,
                ];
            });
        } else {
            $openTasksCount = 9;
            $tasks = collect([
                (object) ['title' => 'Текст объявлений для Директа', 'role_label' => 'Копирайтер', 'due_label' => 'Сегодня, 16:00', 'is_done' => false],
                (object) ['title' => 'Баннеры для ВК — 3 размера', 'role_label' => 'Дизайнер', 'due_label' => 'Сегодня, 18:00', 'is_done' => false],
                (object) ['title' => 'Согласовать бюджет «Открытие точки»', 'role_label' => 'Менеджер', 'due_label' => 'Завтра', 'is_done' => false],
                (object) ['title' => 'Еженедельный отчёт по ROI', 'role_label' => 'Менеджер', 'due_label' => '25 ноября', 'is_done' => true],
            ]);
        }

        return view('dashboard', [
            'search' => $search,
            'selectedRole' => $selectedRole,
            'roleLabels' => $roleLabels,
            'displayUserName' => $displayUserName,
            'displayUserInitials' => $displayUserInitials,
            'displayUserRole' => $displayUserRole,
            'campaigns' => $campaigns,
            'totalCampaignsCount' => $totalCampaignsCount,
            'activeCampaignsCount' => $activeCampaignsCount,
            'totalBudget' => $totalBudget,
            'totalSpent' => $totalSpent,
            'totalRevenue' => $totalRevenue,
            'budgetUsedPercent' => $budgetUsedPercent,
            'budgetLeftPercent' => $budgetLeftPercent,
            'roi' => $roi,
            'newLeadsCount' => $newLeadsCount,
            'openTasksCount' => $openTasksCount,
            'tasks' => $tasks,
        ]);
    }
}