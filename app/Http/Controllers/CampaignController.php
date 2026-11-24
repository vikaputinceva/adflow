<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignMaterial;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = trim((string) $request->get('search', ''));

        $query = Campaign::query();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('platform_name', 'like', '%' . $search . '%');
            });
        }

        $campaigns = $query->orderBy('id', 'desc')->get();

        foreach ($campaigns as $campaign) {
            $campaign->spent_percent = $campaign->budget_total > 0
                ? round(($campaign->budget_spent / $campaign->budget_total) * 100)
                : 0;

            $campaign->roi = $campaign->budget_spent > 0
                ? round((($campaign->revenue_attributed - $campaign->budget_spent) / $campaign->budget_spent) * 100)
                : 0;
        }

        $selectedId = $request->get('selected');
        $selectedCampaign = $campaigns->firstWhere('id', $selectedId) ?? $campaigns->first();

        $materials = $selectedCampaign
            ? $selectedCampaign->materials()->orderBy('id', 'desc')->get()
            : collect();

        $allCampaigns = Campaign::all();
        $totalBudget = $allCampaigns->sum('budget_total');
        $totalSpent = $allCampaigns->sum('budget_spent');

        $budgetUsedPercent = $totalBudget > 0
            ? min(100, max(0, round(($totalSpent / $totalBudget) * 100)))
            : 0;
        $budgetLeftPercent = max(0, 100 - $budgetUsedPercent);

        return view('campaigns.index', [
            'campaigns' => $campaigns,
            'selectedCampaign' => $selectedCampaign,
            'materials' => $materials,
            'currentStatus' => $status,
            'search' => $search,
            'budgetUsedPercent' => $budgetUsedPercent,
            'budgetLeftPercent' => $budgetLeftPercent,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'platform_name' => 'required|string|max:100',
            'budget_total' => 'required|numeric|min:1',
            'end_date' => 'required|date',
        ]);

        Campaign::create([
            'name' => $request->name,
            'platform_name' => $request->platform_name,
            'status' => 'active',
            'budget_total' => $request->budget_total,
            'budget_spent' => 0,
            'revenue_attributed' => 0,
            'end_date' => $request->end_date,
        ]);

        return redirect('/campaigns')->with('success', 'Кампания добавлена');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,paused,completed',
        ]);

        $campaign = Campaign::findOrFail($id);
        $campaign->status = $request->status;
        $campaign->save();

        return redirect('/campaigns?selected=' . $campaign->id)->with('success', 'Статус обновлён');
    }

    public function updateMetrics(Request $request, $id)
    {
        $request->validate([
            'budget_spent' => 'required|numeric|min:0',
            'revenue_attributed' => 'required|numeric|min:0',
        ]);

        $campaign = Campaign::findOrFail($id);
        $campaign->budget_spent = $request->budget_spent;
        $campaign->revenue_attributed = $request->revenue_attributed;
        $campaign->save();

        return redirect('/campaigns?selected=' . $campaign->id)->with('success', 'Показатели обновлены');
    }

    public function storeMaterial(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:120',
            'format_label' => 'required|string|max:60',
        ]);

        $campaign = Campaign::findOrFail($id);

        CampaignMaterial::create([
            'campaign_id' => $campaign->id,
            'title' => $request->title,
            'format_label' => $request->format_label,
        ]);

        return redirect('/campaigns?selected=' . $campaign->id)->with('success', 'Материал прикреплён');
    }

    public function destroy($id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->delete();

        return redirect('/campaigns')->with('success', 'Кампания удалена');
    }
}