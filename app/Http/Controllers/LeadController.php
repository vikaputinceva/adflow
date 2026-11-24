<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\LeadRequest;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = trim((string) $request->get('search', ''));

        $query = LeadRequest::with('campaign')->orderByDesc('id');

        if (array_key_exists($status, LeadRequest::STATUSES)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', '%' . $search . '%')
                  ->orWhere('contact', 'like', '%' . $search . '%');
            });
        }

        return view('leads.index', [
            'leads' => $query->get(),
            'currentStatus' => $status,
            'search' => $search,
            'statuses' => LeadRequest::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_name' => 'required|string|max:150',
            'contact' => 'required|string|max:150',
            'platform_name' => 'required|string|max:100',
            'budget_estimate' => 'required|numeric|min:0',
        ]);

        LeadRequest::create($data + ['status' => 'new']);

        return redirect('/leads')->with('success', 'Заявка создана');
    }

    public function updateStatus(Request $request, LeadRequest $lead)
    {
        $data = $request->validate([
            'status' => 'required|in:new,in_work,done',
        ]);

        $lead->update($data);

        return back()->with('success', 'Статус заявки обновлён');
    }

    // Превращаем заявку в рекламную кампанию
    public function convert(LeadRequest $lead)
    {
        if ($lead->campaign_id) {
            return back()->with('success', 'Кампания по этой заявке уже создана');
        }

        $campaign = Campaign::create([
            'name' => $lead->client_name,
            'platform_name' => $lead->platform_name,
            'status' => 'active',
            'budget_total' => max(1, $lead->budget_estimate),
            'budget_spent' => 0,
            'revenue_attributed' => 0,
            'end_date' => now()->addDays(30)->toDateString(),
        ]);

        $lead->update([
            'campaign_id' => $campaign->id,
            'status' => 'in_work',
        ]);

        return redirect('/campaigns?selected=' . $campaign->id)->with('success', 'Кампания создана из заявки');
    }

    public function destroy(LeadRequest $lead)
    {
        $lead->delete();

        return back()->with('success', 'Заявка удалена');
    }
}
