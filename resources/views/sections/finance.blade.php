<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdFlow — Финансы</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Unbounded:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="container">
    <div class="page-layout">
        <aside class="sidebar">
            <div class="logo-box">
                <div class="logo-badge"></div>
                <div>
                    <p class="logo-title">AdFlow</p>
                    <p class="logo-sub">Реклама малого бизнеса</p>
                </div>
            </div>
            <nav class="nav-list">
                <a href="/campaigns" class="nav-link" title="Реестр кампаний">Кампании</a>
                <a href="/tasks" class="nav-link" title="Задачи и сроки">Задачи</a>
                <a href="/finance" class="nav-link active" title="Бюджеты и лимиты">Финансы</a>
                <a href="/reports" class="nav-link" title="Отчёты, KPI и ROI">Отчёты</a>
                <a href="/login" class="nav-link" title="Вход">Вход</a>
            </nav>
            <div class="sidebar-limit">
                <p class="sidebar-limit-title">Лимит бюджета</p>
                <p class="sidebar-limit-num">{{ $budgetLeftPercent ?? 0 }}%</p>
                <div class="progress-track">
                    <div class="progress-fill" style="width: {{ $budgetUsedPercent ?? 0 }}%;"></div>
                </div>
                <p class="sidebar-limit-note">Освоено {{ $budgetUsedPercent ?? 0 }}% общего бюджета</p>
            </div>
            <div class="user-card">
                <div class="user-avatar">АК</div>
                <div>
                    <p class="user-name">{{ auth()->user()->name ?? 'Анна К.' }}</p>
                    <p class="user-role">{{ auth()->user()->role ?? 'Менеджер' }}</p>
                </div>
            </div>
            <form method="POST" action="/logout" style="padding: 0 8px;">
                @csrf
                <button type="submit" class="btn-outline" style="width: 100%;">Выйти</button>
            </form>
        </aside>

        <main class="content">
            <header class="topbar">
                <p class="section-label">Финансы</p>
                <a href="/campaigns" class="btn-outline">К кампаниям</a>
            </header>

            <div class="page-head">
                <div>
                    <h1 class="page-title">Контроль бюджетов и лимитов</h1>
                    <p class="page-desc">Управление бюджетами кампаний, остатком средств и выручкой.</p>
                </div>
            </div>

            @if(!empty($budgetAlerts))
                <div class="card" style="padding: 14px 20px; background: #FFF4E5; color: #7A3E00;">
                    <strong>Уведомления о лимитах бюджета:</strong>
                    <ul style="margin: 6px 0 0 18px;">
                        @foreach($budgetAlerts as $alert)
                            <li>{{ $alert }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="card" style="padding: 14px 20px; font-weight: 600;">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="card" style="padding: 14px 20px; background: #FFE9D6; color: #8A3B00; font-weight: 600;">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="card" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
                <div>
                    <p class="page-desc">Общий бюджет</p>
                    <h2 class="page-title">{{ number_format($totalBudget, 0, ',', ' ') }} ₽</h2>
                </div>
                <div>
                    <p class="page-desc">Потрачено</p>
                    <h2 class="page-title">{{ number_format($totalSpent, 0, ',', ' ') }} ₽</h2>
                </div>
                <div>
                    <p class="page-desc">Остаток лимита</p>
                    <h2 class="page-title">{{ number_format($totalRemaining, 0, ',', ' ') }} ₽</h2>
                </div>
                <div>
                    <p class="page-desc">Выручка / Прибыль</p>
                    <h2 class="page-title">{{ number_format($totalRevenue, 0, ',', ' ') }} ₽</h2>
                    <p class="mini-sub">Прибыль: {{ number_format($totalProfit, 0, ',', ' ') }} ₽</p>
                </div>
            </div>

            <div class="card table-card">
                <table class="campaigns-table">
                    <thead>
                        <tr>
                            <th>Кампания</th>
                            <th>Бюджет (₽)</th>
                            <th>Потрачено (₽)</th>
                            <th>Выручка (₽)</th>
                            <th class="text-right">Действие</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($campaigns as $campaign)
                            <tr>
                                <td>
                                    <p class="camp-title">{{ $campaign->name }}</p>
                                    <p class="camp-date">
                                        {{ $campaign->platform_name }} · освоено {{ $campaign->spent_percent }}% · остаток {{ number_format($campaign->remaining_budget, 0, ',', ' ') }} ₽
                                    </p>
                                </td>
                                <td colspan="4">
                                    <form method="POST" action="/finance/{{ $campaign->id }}/budget" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 8px; align-items: center;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="budget_total" value="{{ $campaign->budget_total }}" min="1" step="0.01" class="field-input" title="Общий бюджет" required>
                                        <input type="number" name="budget_spent" value="{{ $campaign->budget_spent }}" min="0" step="0.01" class="field-input" title="Потрачено" required>
                                        <input type="number" name="revenue_attributed" value="{{ $campaign->revenue_attributed }}" min="0" step="0.01" class="field-input" title="Выручка" required>
                                        <button type="submit" class="btn-dark">Сохранить</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>
</body>
</html>