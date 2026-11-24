<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    // Пускаем только пользователей с нужной ролью, например role:admin,manager
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $roles, true)) {
            abort(403, 'Нет доступа');
        }

        return $next($request);
    }
}
