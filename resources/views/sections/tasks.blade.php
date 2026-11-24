<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdFlow — Задачи отдела</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Unbounded:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
@php
    $roleFilter = $roleFilter ?? request('role', 'all');
    $viewMode = request('view', 'list');
    $searchQuery = trim((string) request('search', ''));
    $selectedAccessRole = request('access_role', 'executor');

    $accessRoles = [
        'admin' => 'Администратор',
        'manager' => 'Менеджер',
        'executor' => 'Исполнитель',
    ];

    $canManageTasks = in_array($selectedAccessRole, ['admin', 'manager'], true);

    $demoTasks = collect([
        (object) [
            'id' => 1,
            'title' => 'Текст объявлений для Директа',
            'campaign_name' => 'Весенняя распродажа',
            'status_label' => 'В работе',
            'role_key' => 'copywriter',
            'role_label' => 'Копирайтер',
            'due_label' => 'Сегодня, 16:00',
            'urgent_date' => true,
            'is_done' => false,
        ],
        (object) [
            'id' => 2,
            'title' => 'Баннеры для ВК — 3 размера',
            'campaign_name' => 'Открытие точки',
            'status_label' => 'На проверке',
            'role_key' => 'designer',
            'role_label' => 'Дизайнер',
            'due_label' => 'Сегодня, 18:00',
            'urgent_date' => true,
            'is_done' => false,
        ],
        (object) [
            'id' => 3,
            'title' => 'Согласовать бюджет «Открытие точки»',
            'campaign_name' => 'Открытие точки',
            'status_label' => 'Новые',
            'role_key' => 'manager',
            'role_label' => 'Менеджер',
            'due_label' => 'Завтра',
            'urgent_date' => false,
            'is_done' => false,
        ],
        (object) [
            'id' => 4,
            'title' => 'Еженедельный отчёт по ROI',
            'campaign_name' => 'Все активные кампании',
            'status_label' => 'Выполнено',
            'role_key' => 'manager',
            'role_label' => 'Менеджер',
            'due_label' => '25 ноября',
            'urgent_date' => false,
            'is_done' => true,
        ],
    ]);

    if (isset($tasks) && $tasks->count() > 0) {
        $listTasks = $tasks->map(function ($task) {
            $roleMap = [
                'designer' => ['designer', 'Дизайнер'],
                'copywriter' => ['copywriter', 'Копирайтер'],
                'manager' => ['manager', 'Менеджер'],
                'executor' => ['designer', 'Дизайнер'],
            ];
            $rk = $task->assignee_role ?? 'designer';
            [$roleKey, $roleLabel] = $roleMap[$rk] ?? ['designer', 'Дизайнер'];

            return (object) [
                'id' => $task->id,
                'title' => $task->title,
                'campaign_name' => $task->campaign->name ?? 'Весенняя распродажа',
                'status_label' => $task->is_done ? 'Выполнено' : 'В работе',
                'role_key' => $roleKey,
                'role_label' => $roleLabel,
                'due_label' => $task->due_date ? date('d.m.Y', strtotime($task->due_date)) : 'Сегодня, 18:00',
                'urgent_date' => !$task->is_done,
                'is_done' => (bool) $task->is_done,
            ];
        });
    } else {
        $listTasks = $demoTasks;
    }

    if ($roleFilter !== 'all') {
        $listTasks = $listTasks->filter(function ($item) use ($roleFilter) {
            return $item->role_key === $roleFilter;
        });
    }

    if ($searchQuery !== '') {
        $listTasks = $listTasks->filter(function ($item) use ($searchQuery) {
            return stripos($item->title, $searchQuery) !== false
                || stripos($item->campaign_name, $searchQuery) !== false;
        });
    }

    if (isset($displayUserName) && !empty($displayUserName)) {
        $currentUserName = $displayUserName;
    } elseif ($selectedAccessRole === 'executor') {
        $currentUserName = 'Дмитрий В.';
    } else {
        $currentUserName = auth()->user()->name ?? 'Алексей С.';
    }

    $currentUserRole = $accessRoles[$selectedAccessRole] ?? 'Исполнитель';
    $words = explode(' ', trim($currentUserName));
    $initials = mb_strtoupper(mb_substr($words[0] ?? 'Д', 0, 1) . mb_substr($words[1] ?? ($words[0] ?? 'В'), 0, 1));
@endphp

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
                <a href="/" class="nav-link">Главная</a>
                <a href="/campaigns" class="nav-link">Кампании</a>
                <a href="/tasks" class="nav-link active">Задачи</a>
                <a href="/finance" class="nav-link">Финансы</a>
                <a href="/reports" class="nav-link">Отчёты</a>
            </nav>

            <div class="role-pill-row">
                <p class="sidebar-limit-title" style="width: 100%; padding: 0 2px;">РОЛЬ ДОСТУПА (ТЗ)</p>
                @foreach($accessRoles as $rKey => $rTitle)
                    <a href="/tasks?role={{ $roleFilter }}&view={{ $viewMode }}&access_role={{ $rKey }}"
                       class="role-chip {{ $selectedAccessRole === $rKey ? 'is-active' : '' }}">
                        {{ $rTitle }}
                    </a>
                @endforeach
            </div>

            <div class="sidebar-limit">
                <p class="sidebar-limit-title">Лимит бюджета</p>
                <p class="sidebar-limit-num">{{ isset($budgetLeftPercent) ? $budgetLeftPercent : 32 }}%</p>
                <div class="progress-track">
                    <div class="progress-fill" style="width: {{ isset($budgetUsedPercent) ? $budgetUsedPercent : 68 }}%;"></div>
                </div>
                <p class="sidebar-limit-note">Осталось до конца месяца</p>
            </div>

            <div class="user-card">
                <div class="user-avatar">{{ $initials }}</div>
                <div>
                    <p class="user-name">{{ $currentUserName }}</p>
                    <p class="user-role">{{ $currentUserRole }}</p>
                </div>
                <form method="POST" action="/logout" style="margin-left: auto;">
                    @csrf
                    <button type="submit" class="btn-outline" style="padding: 6px 10px;" title="Выйти">→</button>
                </form>
            </div>
        </aside>

        <main class="content">
            <header class="topbar">
                <p class="section-label">ЗАДАЧИ</p>
                <div class="topbar-controls">
                    <form method="GET" action="/tasks" style="display: flex; gap: 8px; align-items: center;">
                        <input type="hidden" name="role" value="{{ $roleFilter }}">
                        <input type="hidden" name="view" value="{{ $viewMode }}">
                        <input type="hidden" name="access_role" value="{{ $selectedAccessRole }}">
                        <input type="search"
                               name="search"
                               value="{{ $searchQuery }}"
                               placeholder="Поиск по заявкам и кампаниям"
                               class="search-input">
                    </form>
                    <a href="/tasks" class="btn-outline" style="padding: 9px 12px;" title="Уведомления">🔔</a>
                </div>
            </header>

            <div class="page-head">
                <div>
                    <h1 class="page-title">Задачи отдела</h1>
                    <p class="page-desc">Постановка задач дизайнерам и копирайтерам, согласование креативов.</p>
                </div>

                <div class="filter-pills">
                    <a href="/tasks?role=all&view={{ $viewMode }}&access_role={{ $selectedAccessRole }}{{ $searchQuery ? '&search=' . urlencode($searchQuery) : '' }}"
                       class="filter-pill {{ $roleFilter === 'all' ? 'active' : '' }}">Все</a>
                    <a href="/tasks?role=designer&view={{ $viewMode }}&access_role={{ $selectedAccessRole }}{{ $searchQuery ? '&search=' . urlencode($searchQuery) : '' }}"
                       class="filter-pill {{ $roleFilter === 'designer' ? 'active' : '' }}">Дизайнер</a>
                    <a href="/tasks?role=copywriter&view={{ $viewMode }}&access_role={{ $selectedAccessRole }}{{ $searchQuery ? '&search=' . urlencode($searchQuery) : '' }}"
                       class="filter-pill {{ $roleFilter === 'copywriter' ? 'active' : '' }}">Копирайтер</a>
                    <a href="/tasks?role=manager&view={{ $viewMode }}&access_role={{ $selectedAccessRole }}{{ $searchQuery ? '&search=' . urlencode($searchQuery) : '' }}"
                       class="filter-pill {{ $roleFilter === 'manager' ? 'active' : '' }}">Менеджер</a>
                    <a href="/tasks?role={{ $roleFilter }}&view={{ $viewMode === 'kanban' ? 'list' : 'kanban' }}&access_role={{ $selectedAccessRole }}{{ $searchQuery ? '&search=' . urlencode($searchQuery) : '' }}"
                       class="filter-pill {{ $viewMode === 'kanban' ? 'active' : '' }}">Вид: Канбан</a>
                </div>
            </div>

            @if(session('success'))
                <div class="card" style="padding: 14px 20px; font-weight: 600;">
                    {{ session('success') }}
                </div>
            @endif

            @if($canManageTasks && isset($campaigns) && $campaigns->count() > 0)
                <div class="card">
                    <h2 class="card-title mb-14">Назначить задачу</h2>
                    <form method="POST" action="/tasks" style="display: grid; grid-template-columns: 1fr 1.5fr 1fr 1fr auto; gap: 10px; align-items: end;">
                        @csrf
                        <label class="field-label">Кампания
                            <select name="campaign_id" class="field-input" required>
                                @foreach($campaigns as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="field-label">Задача
                            <input type="text" name="title" placeholder="Подготовить баннер" required class="field-input">
                        </label>
                        <label class="field-label">Роль
                            <select name="assignee_role" class="field-input">
                                <option value="designer">Дизайнер</option>
                                <option value="copywriter">Копирайтер</option>
                                <option value="manager">Менеджер</option>
                            </select>
                        </label>
                        <label class="field-label">Срок
                            <input type="date" name="due_date" class="field-input">
                        </label>
                        <button type="submit" class="btn-dark">Добавить</button>
                    </form>
                </div>
            @endif

            <section class="tasks-board-wrap">
                @if($viewMode === 'kanban')
                    <div class="kanban-grid">
                        <div class="kanban-col">
                            <p class="kanban-col-title">Новые / В работе</p>
                            @foreach($listTasks->where('is_done', false) as $item)
                                <div class="task-card-row" style="background: #FFFFFF; padding: 12px 14px;">
                                    <div>
                                        <p class="task-card-title">{{ $item->title }}</p>
                                        <p class="task-card-sub">Кампания: {{ $item->campaign_name }} · Статус: {{ $item->status_label }}</p>
                                        <div style="margin-top: 8px; display: flex; gap: 6px; flex-wrap: wrap;">
                                            <span class="task-role-badge">{{ $item->role_label }}</span>
                                            <span class="task-date-badge {{ $item->urgent_date ? '' : 'is-calm' }}">{{ $item->due_label }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="kanban-col">
                            <p class="kanban-col-title">На согласовании</p>
                            @foreach($listTasks->where('status_label', 'На проверке') as $item)
                                <div class="task-card-row" style="background: #FFFFFF; padding: 12px 14px;">
                                    <div>
                                        <p class="task-card-title">{{ $item->title }}</p>
                                        <p class="task-card-sub">Кампания: {{ $item->campaign_name }} · Статус: {{ $item->status_label }}</p>
                                        <div style="margin-top: 8px; display: flex; gap: 6px; flex-wrap: wrap;">
                                            <span class="task-role-badge">{{ $item->role_label }}</span>
                                            <span class="task-date-badge {{ $item->urgent_date ? '' : 'is-calm' }}">{{ $item->due_label }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="kanban-col">
                            <p class="kanban-col-title">Выполнено</p>
                            @foreach($listTasks->where('is_done', true) as $item)
                                <div class="task-card-row is-done" style="background: #FFFFFF; padding: 12px 14px;">
                                    <div>
                                        <p class="task-card-title">{{ $item->title }}</p>
                                        <p class="task-card-sub">Кампания: {{ $item->campaign_name }} · Статус: {{ $item->status_label }}</p>
                                        <div style="margin-top: 8px; display: flex; gap: 6px; flex-wrap: wrap;">
                                            <span class="task-role-badge">{{ $item->role_label }}</span>
                                            <span class="task-date-badge is-calm">{{ $item->due_label }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    @forelse($listTasks as $item)
                        <div class="task-card-row {{ $item->is_done ? 'is-done' : '' }}">
                            <div class="task-card-main">
                                <form method="POST" action="/tasks/{{ $item->id }}/toggle" style="display: inline-flex; align-items: center;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="task-check-btn {{ $item->is_done ? 'is-done' : '' }}"
                                            title="Отметить выполнение"></button>
                                </form>
                                <div>
                                    <p class="task-card-title">{{ $item->title }}</p>
                                    <p class="task-card-sub">Кампания: {{ $item->campaign_name }} · Статус: {{ $item->status_label }}</p>
                                </div>
                            </div>

                            <div class="task-card-right">
                                <span class="task-role-badge">{{ $item->role_label }}</span>
                                <span class="task-date-badge {{ $item->urgent_date ? '' : 'is-calm' }}">{{ $item->due_label }}</span>
                                @if($canManageTasks)
                                    <form method="POST" action="/tasks/{{ $item->id }}" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-outline" style="padding: 4px 10px;">×</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="task-card-row">
                            <p class="task-card-sub">Задач по выбранному фильтру пока нет.</p>
                        </div>
                    @endforelse
                @endif
            </section>
        </main>

    </div>
</div>
</body>
</html>