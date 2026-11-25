<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Platform;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    public function index()
    {
        $platforms = Platform::orderBy('name')->get();

        // сколько кампаний на каждой площадке
        $counts = Campaign::selectRaw('platform_name, count(*) as total')
            ->groupBy('platform_name')
            ->pluck('total', 'platform_name');

        return view('platforms.index', [
            'platforms' => $platforms,
            'counts' => $counts,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:platforms,name',
        ]);

        Platform::create($data);

        return redirect('/platforms')->with('success', 'Площадка добавлена');
    }

    public function destroy(Platform $platform)
    {
        if ($platform->isUsed()) {
            return back()->withErrors(['platform' => 'Нельзя удалить площадку, по ней уже есть кампании или заявки']);
        }

        $platform->delete();

        return redirect('/platforms')->with('success', 'Площадка удалена');
    }
}
