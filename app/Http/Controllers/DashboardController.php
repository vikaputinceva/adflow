<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignTask;
use App\Models\LeadRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // у исполнителя главная страница это его задачи
        if ($user->role === 'executor') {
            return redirect('/tasks');
        }

        $search = trim((string) $request->get('search', ''));

        $query = Campaign::orderByDesc('budget_spent');
        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }
        $campaigns = $query->get();

        $totals = Campaign::totals();

        // уведомления: лимиты бюджета и сроки задач
        $alerts = [];
        foreach (Campaign::all() as $campaign) {
            $text = $campaign->limitMessage();
            if ($text) {
                $alerts[] = ['type' => 'Лимит', 'text' => $text];
            }
        }

        $urgentTasksCount = 0;
        foreach (CampaignTask::where('is_done', false)->get() as $task) {
            $text = $task->deadlineMessage();
            if ($text) {
                $alerts[] = ['type' => 'Срок', 'text' => $text];
                $urgentTasksCount++;
            }
        }

        // «Мои задачи»: у менеджера свои и задачи для менеджера, у админа все открытые
        $tasksQuery = CampaignTask::with('campaign')
            ->where('is_done', false)
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date');

        if ($user->role === 'manager') {
            $tasksQuery->where(function ($q) use ($user) {
                $q->where('assignee_role', 'manager')->orWhere('user_id', $user->id);
            });
        }

        return view('dashboard', [
            'search' => $search,
            'campaigns' => $campaigns,
            'totalCampaignsCount' => Campaign::count(),
            'activeCampaignsCount' => Campaign::where('status', 'active')->count(),
            'totalBudget' => $totals['budget'],
            'totalSpent' => $totals['spent'],
            'totalRevenue' => $totals['revenue'],
            'budgetUsedPercent' => $totals['used_percent'],
            'budgetLeftPercent' => $totals['left_percent'],
            'roi' => $totals['roi'],
            'newLeadsCount' => LeadRequest::where('status', 'new')->count(),
            'newLeadsWeek' => LeadRequest::where('created_at', '>=', now()->subDays(7))->count(),
            'openTasksCount' => CampaignTask::where('is_done', false)->count(),
            'urgentTasksCount' => $urgentTasksCount,
            'tasks' => $tasksQuery->take(4)->get(),
            'alerts' => array_slice($alerts, 0, 4),
        ]);
    }
}
