<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignMaterial;
use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        // выбранная кампания (по умолчанию первая в списке)
        $selectedCampaign = $campaigns->firstWhere('id', $request->get('selected')) ?? $campaigns->first();

        $materials = $selectedCampaign
            ? $selectedCampaign->materials()->orderBy('id', 'desc')->get()
            : collect();

        $totals = Campaign::totals();

        return view('campaigns.index', [
            'campaigns' => $campaigns,
            'platforms' => Platform::orderBy('name')->get(),
            'selectedCampaign' => $selectedCampaign,
            'materials' => $materials,
            'currentStatus' => $status,
            'search' => $search,
            'budgetUsedPercent' => $totals['used_percent'],
            'budgetLeftPercent' => $totals['left_percent'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'platform_name' => 'required|exists:platforms,name',
            'budget_total' => 'required|numeric|min:1',
            'end_date' => 'required|date',
        ]);

        Campaign::create($data + [
            'status' => 'active',
            'budget_spent' => 0,
            'revenue_attributed' => 0,
        ]);

        return redirect('/campaigns')->with('success', 'Кампания добавлена');
    }

    public function updateStatus(Request $request, Campaign $campaign)
    {
        $data = $request->validate([
            'status' => 'required|in:active,paused,completed',
        ]);

        $campaign->update($data);

        return redirect('/campaigns?selected=' . $campaign->id)->with('success', 'Статус обновлён');
    }

    public function updateMetrics(Request $request, Campaign $campaign)
    {
        $data = $request->validate([
            'budget_spent' => 'required|numeric|min:0',
            'revenue_attributed' => 'required|numeric|min:0',
        ]);

        $campaign->update($data);

        return redirect('/campaigns?selected=' . $campaign->id)->with('success', 'Показатели обновлены');
    }

    public function storeMaterial(Request $request, Campaign $campaign)
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'format_label' => 'required|string|max:60',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,mp4|max:5120',
        ]);

        $material = new CampaignMaterial([
            'title' => $data['title'],
            'format_label' => $data['format_label'],
        ]);
        $material->campaign_id = $campaign->id;

        // файл не обязателен: можно прикрепить только название и формат
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $material->file_path = $file->store('materials', 'public');
            $material->file_name = $file->getClientOriginalName();
        }

        $material->save();

        return redirect('/campaigns?selected=' . $campaign->id)->with('success', 'Материал прикреплён');
    }

    public function destroyMaterial(CampaignMaterial $material)
    {
        $campaignId = $material->campaign_id;

        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return redirect('/campaigns?selected=' . $campaignId)->with('success', 'Материал удалён');
    }

    public function destroy(Campaign $campaign)
    {
        // файлы материалов лежат на диске, строки в базе удалятся сами
        foreach ($campaign->materials as $material) {
            if ($material->file_path) {
                Storage::disk('public')->delete($material->file_path);
            }
        }

        $campaign->delete();

        return redirect('/campaigns')->with('success', 'Кампания удалена');
    }
}
