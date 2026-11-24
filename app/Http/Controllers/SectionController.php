<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignTask;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    private function baseData(): array
    {
        $campaigns = Campaign::orderByDesc('id')->get();

        foreach ($campaigns as $campaign) {
            $campaign->spent_percent = $campaign->budget_total > 0
                ? round(($campaign->budget_spent / $campaign->budget_total) * 100)
                : 0;

            $campaign->remaining_budget = max(0, $campaign->budget_total - $campaign->budget_spent);
            $campaign->profit = $campaign->revenue_attributed - $campaign->budget_spent;
            $campaign->roi = $campaign->budget_spent > 0
                ? round((($campaign->revenue_attributed - $campaign->budget_spent) / $campaign->budget_spent) * 100)
                : 0;
        }

        $totalBudget = $campaigns->sum('budget_total');
        $totalSpent = $campaigns->sum('budget_spent');
        $totalRevenue = $campaigns->sum('revenue_attributed');
        $totalRemaining = max(0, $totalBudget - $totalSpent);
        $totalProfit = $totalRevenue - $totalSpent;

        $overallRoi = $totalSpent > 0
            ? round((($totalRevenue - $totalSpent) / $totalSpent) * 100)
            : 0;

        $usedPercent = $totalBudget > 0
            ? min(100, max(0, round(($totalSpent / $totalBudget) * 100)))
            : 0;
        $leftPercent = max(0, 100 - $usedPercent);

        $budgetAlerts = [];
        foreach ($campaigns as $campaign) {
            if ($campaign->spent_percent >= 85) {
                $budgetAlerts[] = "Лимит бюджета кампании «{$campaign->name}» освоен на {$campaign->spent_percent}% (остаток " . number_format($campaign->remaining_budget, 0, ',', ' ') . " ₽).";
            }
        }

        return [
            'campaigns' => $campaigns,
            'totalBudget' => $totalBudget,
            'totalSpent' => $totalSpent,
            'totalRevenue' => $totalRevenue,
            'totalRemaining' => $totalRemaining,
            'totalProfit' => $totalProfit,
            'overallRoi' => $overallRoi,
            'budgetUsedPercent' => $usedPercent,
            'budgetLeftPercent' => $leftPercent,
            'budgetAlerts' => $budgetAlerts,
        ];
    }

    public function tasks(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $roleFilter = $request->get('role', 'all');
        $data = $this->baseData();

        $query = CampaignTask::with('campaign')
            ->orderBy('is_done')
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->orderByDesc('id');

        if ($filter === 'open') {
            $query->where('is_done', false);
        } elseif ($filter === 'done') {
            $query->where('is_done', true);
        }

        if (in_array($roleFilter, ['manager', 'executor'], true)) {
            $query->where('assignee_role', $roleFilter);
        }

        $tasks = $query->get();
        $today = Carbon::today();

        $deadlineAlerts = [];
        foreach ($tasks as $task) {
            if (!$task->is_done && $task->due_date) {
                $due = Carbon::parse($task->due_date)->startOfDay();
                $diffDays = $today->diffInDays($due, false);
                if ($diffDays < 0) {
                    $deadlineAlerts[] = "Просрочена задача «{$task->title}» (срок был " . $due->format('d.m.Y') . ").";
                } elseif ($diffDays <= 3) {
                    $deadlineAlerts[] = "Приближается срок задачи «{$task->title}» (" . $due->format('d.m.Y') . ").";
                }
            }
        }

        $data['tasks'] = $tasks;
        $data['filter'] = $filter;
        $data['roleFilter'] = $roleFilter;
        $data['openCount'] = CampaignTask::where('is_done', false)->count();
        $data['doneCount'] = CampaignTask::where('is_done', true)->count();
        $data['deadlineAlerts'] = $deadlineAlerts;

        return view('sections.tasks', $data);
    }

    public function storeTask(Request $request)
    {
        $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'title' => 'required|string|max:200',
            'assignee_role' => 'required|in:manager,executor',
            'due_date' => 'nullable|date',
        ]);

        CampaignTask::create([
            'campaign_id' => $request->campaign_id,
            'title' => $request->title,
            'assignee_role' => $request->assignee_role,
            'due_date' => $request->due_date,
            'is_done' => false,
        ]);

        return redirect('/tasks')->with('success', 'Задача назначена');
    }

    public function toggleTask($id)
    {
        $task = CampaignTask::findOrFail($id);
        $task->is_done = !$task->is_done;
        $task->save();

        return redirect('/tasks')->with('success', 'Статус задачи обновлён');
    }

    public function destroyTask($id)
    {
        $task = CampaignTask::findOrFail($id);
        $task->delete();

        return redirect('/tasks')->with('success', 'Задача удалена');
    }

    public function finance()
    {
        $data = $this->baseData();

        return view('sections.finance', $data);
    }

    public function updateFinance(Request $request, $id)
    {
        $request->validate([
            'budget_total' => 'required|numeric|min:1',
            'budget_spent' => 'required|numeric|min:0',
            'revenue_attributed' => 'required|numeric|min:0',
        ]);

        $campaign = Campaign::findOrFail($id);
        $campaign->budget_total = $request->budget_total;
        $campaign->budget_spent = $request->budget_spent;
        $campaign->revenue_attributed = $request->revenue_attributed;
        $campaign->save();

        return redirect('/finance')->with('success', 'Бюджет и показатели кампании обновлены');
    }

    public function reports()
    {
        $data = $this->baseData();
        $campaigns = $data['campaigns'];

        $allTasks = CampaignTask::with('campaign')->orderByDesc('updated_at')->get();
        $completedTasks = $allTasks->where('is_done', true);
        $totalTasksCount = $allTasks->count();
        $completedTasksCount = $completedTasks->count();

        $taskKpiPercent = $totalTasksCount > 0
            ? round(($completedTasksCount / $totalTasksCount) * 100)
            : 0;

        $byPlatform = $campaigns->groupBy('platform_name')->map(function ($items, $platform) use ($data) {
            $budget = $items->sum('budget_total');
            $spent = $items->sum('budget_spent');
            $revenue = $items->sum('revenue_attributed');
            $profit = $revenue - $spent;
            $roi = $spent > 0 ? round((($revenue - $spent) / $spent) * 100) : 0;
            $budgetShare = $data['totalBudget'] > 0 ? round(($budget / $data['totalBudget']) * 100) : 0;

            return [
                'platform_name' => $platform,
                'count' => $items->count(),
                'budget' => $budget,
                'spent' => $spent,
                'revenue' => $revenue,
                'profit' => $profit,
                'roi' => $roi,
                'budget_share' => $budgetShare,
            ];
        })->values();

        $topCampaign = $campaigns->sortByDesc('roi')->first();

        $data['byPlatform'] = $byPlatform;
        $data['completedTasks'] = $completedTasks;
        $data['totalTasksCount'] = $totalTasksCount;
        $data['completedTasksCount'] = $completedTasksCount;
        $data['taskKpiPercent'] = $taskKpiPercent;
        $data['topCampaign'] = $topCampaign;

        return view('sections.reports', $data);
    }
}