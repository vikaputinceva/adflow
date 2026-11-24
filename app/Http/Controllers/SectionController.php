<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignTask;
use App\Models\User;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    // Общие данные для страниц «Финансы» и «Отчёты»
    private function baseData(): array
    {
        $campaigns = Campaign::orderByDesc('id')->get();
        $totals = Campaign::totals();

        $budgetAlerts = [];
        foreach ($campaigns as $campaign) {
            $message = $campaign->limitMessage();
            if ($message) {
                $budgetAlerts[] = $message;
            }
        }

        return [
            'campaigns' => $campaigns,
            'totalBudget' => $totals['budget'],
            'totalSpent' => $totals['spent'],
            'totalRevenue' => $totals['revenue'],
            'totalRemaining' => $totals['remaining'],
            'totalProfit' => $totals['profit'],
            'overallRoi' => $totals['roi'],
            'budgetUsedPercent' => $totals['used_percent'],
            'budgetLeftPercent' => $totals['left_percent'],
            'budgetAlerts' => $budgetAlerts,
        ];
    }

    public function tasks(Request $request)
    {
        $user = auth()->user();
        $roleFilter = $request->get('role', 'all');
        $viewMode = $request->get('view', 'list');
        $search = trim((string) $request->get('search', ''));

        $query = CampaignTask::visibleTo($user)
            ->with(['campaign', 'user'])
            ->orderBy('is_done')
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->orderByDesc('id');

        if (in_array($roleFilter, ['manager', 'designer', 'copywriter'], true)) {
            $query->where('assignee_role', $roleFilter);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhereHas('campaign', function ($c) use ($search) {
                      $c->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        $tasks = $query->get();

        // уведомления о сроках
        $deadlineAlerts = [];
        foreach ($tasks as $task) {
            $message = $task->deadlineMessage();
            if ($message) {
                $deadlineAlerts[] = $message;
            }
        }

        // ставить задачи могут только админ и менеджер
        $canManageTasks = in_array($user->role, ['admin', 'manager'], true);

        return view('sections.tasks', [
            'tasks' => $tasks,
            'roleFilter' => $roleFilter,
            'viewMode' => $viewMode,
            'searchQuery' => $search,
            'deadlineAlerts' => $deadlineAlerts,
            'canManageTasks' => $canManageTasks,
            'campaigns' => $canManageTasks ? Campaign::orderByDesc('id')->get() : collect(),
            'executors' => $canManageTasks ? User::where('role', 'executor')->orderBy('name')->get() : collect(),
        ]);
    }

    public function storeTask(Request $request)
    {
        $data = $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'user_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:200',
            'assignee_role' => 'required|in:manager,designer,copywriter',
            'due_date' => 'nullable|date',
        ]);

        CampaignTask::create($data + ['is_done' => false]);

        return redirect('/tasks')->with('success', 'Задача назначена');
    }

    public function toggleTask(CampaignTask $task)
    {
        // исполнитель может отмечать только те задачи, которые ему видны
        $allowed = CampaignTask::visibleTo(auth()->user())->where('id', $task->id)->exists();
        if (!$allowed) {
            abort(403);
        }

        $task->is_done = !$task->is_done;
        $task->save();

        return back()->with('success', 'Статус задачи обновлён');
    }

    public function destroyTask(CampaignTask $task)
    {
        $task->delete();

        return redirect('/tasks')->with('success', 'Задача удалена');
    }

    public function finance()
    {
        return view('sections.finance', $this->baseData());
    }

    public function updateFinance(Request $request, Campaign $campaign)
    {
        $data = $request->validate([
            'budget_total' => 'required|numeric|min:1',
            'budget_spent' => 'required|numeric|min:0',
            'revenue_attributed' => 'required|numeric|min:0',
        ]);

        $campaign->update($data);

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

        // KPI = доля выполненных задач
        $taskKpiPercent = $totalTasksCount > 0
            ? round($completedTasksCount / $totalTasksCount * 100)
            : 0;

        // сводка по площадкам
        $byPlatform = $campaigns->groupBy('platform_name')->map(function ($items, $platform) use ($data) {
            $budget = $items->sum('budget_total');
            $spent = $items->sum('budget_spent');
            $revenue = $items->sum('revenue_attributed');

            return [
                'platform_name' => $platform,
                'count' => $items->count(),
                'budget' => $budget,
                'spent' => $spent,
                'revenue' => $revenue,
                'profit' => $revenue - $spent,
                'roi' => $spent > 0 ? round(($revenue - $spent) / $spent * 100) : 0,
                'budget_share' => $data['totalBudget'] > 0 ? round($budget / $data['totalBudget'] * 100) : 0,
            ];
        })->values();

        $data['byPlatform'] = $byPlatform;
        $data['completedTasks'] = $completedTasks;
        $data['totalTasksCount'] = $totalTasksCount;
        $data['completedTasksCount'] = $completedTasksCount;
        $data['taskKpiPercent'] = $taskKpiPercent;
        $data['topCampaign'] = $campaigns->sortByDesc('roi')->first();

        return view('sections.reports', $data);
    }
}
