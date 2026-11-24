<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdFlow — Задачи</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Unbounded:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="container">
    <div class="page-layout">
        @include('partials.sidebar', ['active' => 'tasks'])

        <main class="content">
            <header class="topbar">
                <p class="section-label">{{ $canManageTasks ? 'ЗАДАЧИ' : 'МОИ ЗАДАЧИ' }}</p>
                <div class="topbar-controls">
                    <form method="GET" action="/tasks" style="display: flex; gap: 8px; align-items: center;">
                        <input type="hidden" name="role" value="{{ $roleFilter }}">
                        <input type="hidden" name="view" value="{{ $viewMode }}">
                        <input type="search"
                               name="search"
                               value="{{ $searchQuery }}"
                               placeholder="Поиск по задачам и кампаниям"
                               class="search-input">
                    </form>
                </div>
            </header>

            <div class="page-head">
                <div>
                    <h1 class="page-title">{{ $canManageTasks ? 'Задачи отдела' : 'Мои задачи' }}</h1>
                    <p class="page-desc">
                        @if($canManageTasks)
                            Постановка задач дизайнерам и копирайтерам, контроль сроков.
                        @else
                            Задачи, назначенные вам, и общие задачи для дизайнеров и копирайтеров.
                        @endif
                    </p>
                </div>

                <div class="filter-pills">
                    <a href="{{ request()->fullUrlWithQuery(['role' => 'all']) }}"
                       class="filter-pill {{ $roleFilter === 'all' ? 'active' : '' }}">Все</a>
                    <a href="{{ request()->fullUrlWithQuery(['role' => 'designer']) }}"
                       class="filter-pill {{ $roleFilter === 'designer' ? 'active' : '' }}">Дизайнер</a>
                    <a href="{{ request()->fullUrlWithQuery(['role' => 'copywriter']) }}"
                       class="filter-pill {{ $roleFilter === 'copywriter' ? 'active' : '' }}">Копирайтер</a>
                    <a href="{{ request()->fullUrlWithQuery(['role' => 'manager']) }}"
                       class="filter-pill {{ $roleFilter === 'manager' ? 'active' : '' }}">Менеджер</a>
                    <a href="{{ request()->fullUrlWithQuery(['view' => $viewMode === 'kanban' ? 'list' : 'kanban']) }}"
                       class="filter-pill {{ $viewMode === 'kanban' ? 'active' : '' }}">Вид: Канбан</a>
                </div>
            </div>

            @if(session('success'))
                <div class="card" style="padding: 14px 20px; font-weight: 600;">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="error-box">{{ $errors->first() }}</div>
            @endif

            @if(!empty($deadlineAlerts))
                <div class="card" style="padding: 14px 20px; background: #FFF4E5; color: #7A3E00;">
                    <strong>Уведомления о сроках:</strong>
                    <ul style="margin: 6px 0 0 18px;">
                        @foreach($deadlineAlerts as $alert)
                            <li>{{ $alert }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($canManageTasks && $campaigns->count() > 0)
                <div class="card">
                    <h2 class="card-title mb-14">Назначить задачу</h2>
                    <form method="POST" action="/tasks" style="display: grid; grid-template-columns: 1fr 1.5fr 1fr 1fr 1fr auto; gap: 10px; align-items: end;">
                        @csrf
                        <label class="field-label">Кампания
                            <select name="campaign_id" class="field-input" required>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
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
                        <label class="field-label">Исполнитель
                            <select name="user_id" class="field-input">
                                <option value="">Не назначен</option>
                                @foreach($executors as $executor)
                                    <option value="{{ $executor->id }}">{{ $executor->name }}</option>
                                @endforeach
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
                            <p class="kanban-col-title">В работе</p>
                            @foreach($tasks->where('is_done', false) as $task)
                                <div class="task-card-row" style="background: #FFFFFF; padding: 12px 14px;">
                                    <div>
                                        <p class="task-card-title">{{ $task->title }}</p>
                                        <p class="task-card-sub">Кампания: {{ $task->campaign->name ?? '—' }}</p>
                                        <div style="margin-top: 8px; display: flex; gap: 6px; flex-wrap: wrap;">
                                            <span class="task-role-badge">{{ $task->role_label }}</span>
                                            <span class="task-date-badge {{ $task->deadlineMessage() ? '' : 'is-calm' }}">{{ $task->due_label }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="kanban-col">
                            <p class="kanban-col-title">Выполнено</p>
                            @foreach($tasks->where('is_done', true) as $task)
                                <div class="task-card-row is-done" style="background: #FFFFFF; padding: 12px 14px;">
                                    <div>
                                        <p class="task-card-title">{{ $task->title }}</p>
                                        <p class="task-card-sub">Кампания: {{ $task->campaign->name ?? '—' }}</p>
                                        <div style="margin-top: 8px; display: flex; gap: 6px; flex-wrap: wrap;">
                                            <span class="task-role-badge">{{ $task->role_label }}</span>
                                            <span class="task-date-badge is-calm">{{ $task->due_label }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    @forelse($tasks as $task)
                        <div class="task-card-row {{ $task->is_done ? 'is-done' : '' }}">
                            <div class="task-card-main">
                                <form method="POST" action="/tasks/{{ $task->id }}/toggle" style="display: inline-flex; align-items: center;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="task-check-btn {{ $task->is_done ? 'is-done' : '' }}"
                                            title="Отметить выполнение"></button>
                                </form>
                                <div>
                                    <p class="task-card-title">{{ $task->title }}</p>
                                    <p class="task-card-sub">
                                        Кампания: {{ $task->campaign->name ?? '—' }} ·
                                        Исполнитель: {{ $task->user->name ?? 'не назначен' }} ·
                                        Статус: {{ $task->is_done ? 'Выполнено' : 'В работе' }}
                                    </p>
                                </div>
                            </div>

                            <div class="task-card-right">
                                <span class="task-role-badge">{{ $task->role_label }}</span>
                                <span class="task-date-badge {{ $task->deadlineMessage() ? '' : 'is-calm' }}">{{ $task->due_label }}</span>
                                @if($canManageTasks)
                                    <form method="POST" action="/tasks/{{ $task->id }}" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-outline" style="padding: 4px 10px;" title="Удалить задачу">×</button>
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
