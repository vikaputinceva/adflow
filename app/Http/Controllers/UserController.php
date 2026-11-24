<?php

namespace App\Http\Controllers;

use App\Models\CampaignTask;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index', [
            'users' => User::orderBy('id')->get(),
            'roles' => User::ROLES,
        ]);
    }

    // Пользователей создаёт только администратор
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'role' => 'required|in:admin,manager,executor',
            'password' => 'required|string|min:6',
        ], [], [
            'name' => 'ФИО',
        ]);

        User::create($data);

        return redirect('/users')->with('success', 'Пользователь создан');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'Нельзя удалить самого себя']);
        }

        // задачи удалённого пользователя остаются без исполнителя
        CampaignTask::where('user_id', $user->id)->update(['user_id' => null]);

        $user->delete();

        return redirect('/users')->with('success', 'Пользователь удалён');
    }
}
