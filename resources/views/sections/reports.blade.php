<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdFlow — Отчёты</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Unbounded:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="container">
    <div class="page-layout">
        @include('partials.sidebar', ['active' => 'reports'])

        <main class="content">
            <header class="topbar">
                <p class="section-label">Отчёты</p>
                <div class="topbar-controls">
                    <button type="button" onclick="window.print()" class="btn-outline" title="Сохранить или распечатать отчёт">Печать отчёта</button>
                    <a href="/campaigns" class="btn-dark">К кампаниям</a>
                </div>
            </header>

            <div class="page-head">
                <div>
                    <h1 class="page-title">Отчёты по задачам, KPI и ROI</h1>
                    <p class="page-desc">Сводка выполнения задач, окупаемости вложений и эффективности по площадкам[cite: 2, 3].</p>
                </div>
            </div>

            <div class="card" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div>
                    <p class="page-desc">Общий ROI кампаний</p>
                    <h2 class="page-title">{{ $overallRoi }}%</h2>
                    <p class="mini-sub">Лидер: {{ $topCampaign->name ?? '—' }} ({{ $topCampaign ? $topCampaign->roi . '%' : '0%' }})</p>
                </div>
                <div>
                    <p class="page-desc">KPI выполнения задач</p>
                    <h2 class="page-title">{{ $taskKpiPercent }}%</h2>
                    <p class="mini-sub">Выполнено {{ $completedTasksCount }} из {{ $totalTasksCount }} задач</p>
                </div>
                <div>
                    <p class="page-desc">Освоение бюджета</p>
                    <h2 class="page-title">{{ $budgetUsedPercent }}%</h2>
                    <p class="mini-sub">{{ number_format($totalSpent, 0, ',', ' ') }} ₽ из {{ number_format($totalBudget, 0, ',', ' ') }} ₽</p>
                </div>
            </div>

            <div class="card table-card">
                <h2 class="card-title mb-14">Эффективность по площадкам (ROI и бюджеты)</h2>
                <table class="campaigns-table">
                    <thead>
                        <tr>
                            <th>Площадка</th>
                            <th>Кампаний</th>
                            <th>Доля бюджета</th>
                            <th>Потрачено</th>
                            <th>Выручка</th>
                            <th class="text-right">ROI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($byPlatform as $row)
                            <tr>
                                <td><strong>{{ $row['platform_name'] }}</strong></td>
                                <td>{{ $row['count'] }}</td>
                                <td>{{ $row['budget_share'] }}%</td>
                                <td>{{ number_format($row['spent'], 0, ',', ' ') }} ₽</td>
                                <td>{{ number_format($row['revenue'], 0, ',', ' ') }} ₽</td>
                                <td class="text-right"><strong>{{ $row['roi'] }}%</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card table-card">
                <h2 class="card-title mb-14">Отчёт по выполненным задачам ({{ $completedTasksCount }})</h2>
                <table class="campaigns-table">
                    <thead>
                        <tr>
                            <th>Задача</th>
                            <th>Кампания</th>
                            <th>Исполнитель</th>
                            <th class="text-right">Срок</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($completedTasks as $task)
                            <tr>
                                <td><strong>{{ $task->title }}</strong></td>
                                <td>{{ $task->campaign->name ?? '—' }}</td>
                                <td>{{ $task->assignee_role === 'manager' ? 'Менеджер' : 'Дизайнер / Копирайтер' }}</td>
                                <td class="text-right">{{ $task->due_date ? date('d.m.Y', strtotime($task->due_date)) : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="padding: 20px 8px; text-align: center;">
                                    Выполненных задач пока нет — отметьте их в разделе «Задачи».
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>
</body>
</html>