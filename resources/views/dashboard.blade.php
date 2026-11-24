<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdFlow — Главная</title>
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
                <a href="/" class="nav-link active">Главная</a>
                <a href="/campaigns" class="nav-link">Кампании</a>
                <a href="/tasks" class="nav-link">Задачи</a>
                <a href="/finance" class="nav-link">Финансы</a>
                <a href="/reports" class="nav-link">Отчёты</a>
                <a href="/leads" class="nav-link">Пользователи и API</a>
            </nav>

            <div class="role-pill-row">
                <p class="sidebar-limit-title" style="width: 100%; padding: 0 2px;">РОЛЬ ДОСТУПА (ТЗ)</p>
                @foreach($roleLabels as $roleKey => $roleTitle)
                    <a href="/?role={{ $roleKey }}{{ $search ? '&search=' . urlencode($search) : '' }}"
                       class="role-chip {{ $selectedRole === $roleKey ? 'is-active' : '' }}">
                        {{ $roleTitle }}
                    </a>
                @endforeach
            </div>

            <div class="sidebar-limit">
                <p class="sidebar-limit-title">Лимит бюджета</p>
                <p class="sidebar-limit-num">{{ $budgetLeftPercent }}%</p>
                <div class="progress-track">
                    <div class="progress-fill" style="width: {{ $budgetUsedPercent }}%;"></div>
                </div>
                <p class="sidebar-limit-note">Осталось до конца месяца</p>
            </div>

            <div class="user-card">
                <div class="user-avatar">{{ $displayUserInitials }}</div>
                <div>
                    <p class="user-name">{{ $displayUserName }}</p>
                    <p class="user-role">{{ $displayUserRole }}</p>
                </div>
                <form method="POST" action="/logout" style="margin-left: auto;">
                    @csrf
                    <button type="submit" class="btn-outline" style="padding: 6px 10px;" title="Выйти">→</button>
                </form>
            </div>
        </aside>

        <main class="content">
            <header class="topbar">
                <p class="section-label">ГЛАВНАЯ</p>
                <div class="topbar-controls">
                    <form method="GET" action="/" style="display: flex; gap: 8px; align-items: center;">
                        <input type="hidden" name="role" value="{{ $selectedRole }}">
                        <input type="search"
                               name="search"
                               value="{{ $search }}"
                               placeholder="Поиск по заявкам и кампаниям"
                               class="search-input">
                    </form>
                    <a href="/tasks" class="btn-outline" style="padding: 9px 12px;" title="Уведомления и задачи">🔔</a>
                    <button type="button" id="openLeadModal" class="btn-dark">+ Новая заявка</button>
                </div>
            </header>

            <div class="page-head">
                <div>
                    <h1 class="page-title">Рабочий стол маркетолога</h1>
                    <p class="page-desc">Заявки, кампании и бюджеты на одном экране — без таблиц и переписок.</p>
                </div>
            </div>

            @if(session('success'))
                <div class="card" style="padding: 14px 20px; font-weight: 600;">
                    {{ session('success') }}
                </div>
            @endif

            <section class="hero-bento">
                <div class="hero-roi-card">
                    <div>
                        <span class="pill-tag">ROI · ноябрь</span>
                        <p class="hero-roi-num">{{ $roi }}%</p>
                        <p class="page-desc">окупаемость рекламных кампаний<br>за месяц</p>
                    </div>
                    <div class="hero-roi-watermark">ADFLOW</div>
                </div>

                <div class="hero-right-stack">
                    <div class="card">
                        <span class="pill-tag">Кампании в работе</span>
                        <p class="kpi-num" style="margin: 10px 0 6px;">{{ $activeCampaignsCount }}</p>
                        <p class="kpi-sub">из {{ $totalCampaignsCount }} в реестре · Директ и ВКонтакте</p>
                    </div>

                    <div class="card">
                        <span class="pill-tag">Бюджет месяца</span>
                        <p class="kpi-num" style="margin: 10px 0 8px;">{{ $budgetUsedPercent }}%</p>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: {{ $budgetUsedPercent }}%;"></div>
                        </div>
                        <p class="kpi-sub">{{ number_format($totalSpent, 0, ',', ' ') }} ₽ из {{ number_format($totalBudget, 0, ',', ' ') }} ₽</p>
                    </div>
                </div>
            </section>

            <section class="stat-row-4">
                <div class="stat-box">
                    <p class="stat-box-title">Новые заявки</p>
                    <p class="stat-box-num">{{ $newLeadsCount }}</p>
                    <span class="mini-pill">+12 за неделю</span>
                </div>

                <div class="stat-box">
                    <p class="stat-box-title">Задач на сегодня</p>
                    <p class="stat-box-num">{{ $openTasksCount }}</p>
                    <span class="mini-pill">3 горят</span>
                </div>

                <div class="stat-box">
                    <p class="stat-box-title">Расход</p>
                    <p class="stat-box-num">{{ number_format($totalSpent, 0, ',', ' ') }} ₽</p>
                    <span class="mini-pill">лимит {{ number_format($totalBudget, 0, ',', ' ') }} ₽</span>
                </div>

                <div class="stat-box">
                    <p class="stat-box-title">Доход</p>
                    <p class="stat-box-num">{{ number_format($totalRevenue, 0, ',', ' ') }} ₽</p>
                    <span class="mini-pill">по заявкам</span>
                </div>
            </section>

            <section class="two-col-panel">
                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Расход по кампаниям</h2>
                        <span class="camp-date">₽, ноябрь</span>
                    </div>

                    @php $maxSpent = max(1, (float) $campaigns->max('budget_spent')); @endphp
                    @forelse($campaigns->take(5) as $campaign)
                        @php $width = min(100, round(($campaign->budget_spent / $maxSpent) * 100)); @endphp
                        <div class="bar-item">
                            <div class="bar-item-top">
                                <span>{{ $campaign->name }}</span>
                                <strong>{{ number_format($campaign->budget_spent, 0, ',', ' ') }}</strong>
                            </div>
                            <div class="bar-track-soft">
                                <div class="bar-fill-blue" style="width: {{ $width }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <p class="page-desc">Кампаний по запросу не найдено.</p>
                    @endforelse
                </div>

                <div class="alerts-dark-card">
                    <h2 class="card-title">Уведомления</h2>

                    <span class="dark-alert-badge">Лимит</span>
                    <p class="dark-alert-text">«Весенняя распродажа» израсходовала 90% бюджета</p>

                    <span class="dark-alert-badge">Срок</span>
                    <p class="dark-alert-text">Задача «Баннеры для ВК» — до 18:00 сегодня</p>

                    <span class="dark-alert-badge">Синхр.</span>
                    <p class="dark-alert-text" style="margin-bottom: 0;">
                        Яндекс.Директ обновлён 5 минут назад
                        <span class="dark-alert-sub" style="display: block;">Дублируются в Telegram</span>
                    </p>
                </div>
            </section>

            <section class="two-col-panel">
                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Мои задачи</h2>
                        <a href="/tasks" class="camp-date">Главная → Мои задачи → Детали</a>
                    </div>

                    @foreach($tasks as $task)
                        <div class="my-task-item">
                            <div class="my-task-left">
                                <span class="task-dot {{ !empty($task->is_done) ? 'is-done' : '' }}"></span>
                                <span style="{{ !empty($task->is_done) ? 'text-decoration: line-through; opacity: 0.65;' : '' }}">{{ $task->title }}</span>
                            </div>
                            <div class="my-task-meta">
                                <span class="mini-pill">{{ $task->role_label }}</span>
                                <span class="mini-pill">{{ $task->due_label }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Интеграции</h2>
                    </div>

                    <div class="integration-box">
                        <p class="camp-title">● Яндекс.Директ API</p>
                        <p class="camp-date">Подключено · REST / JSON</p>
                    </div>

                    <div class="integration-box">
                        <p class="camp-title">● ВКонтакте API</p>
                        <p class="camp-date">Подключено · REST / JSON</p>
                    </div>

                    <div class="integration-box">
                        <p class="camp-title">● Telegram-уведомления</p>
                        <p class="camp-date">Сроки задач и лимиты бюджета</p>
                    </div>
                </div>
            </section>
        </main>

    </div>
</div>

<div class="modal-backdrop" id="leadModal">
    <div class="modal-window">
        <div class="modal-head">
            <h2 class="card-title">Новая заявка</h2>
            <button type="button" class="modal-close" id="closeLeadModal" aria-label="Закрыть">&times;</button>
        </div>
        <form method="POST" action="/leads" class="create-form">
            @csrf
            <label class="field-label">Клиент / проект
                <input type="text" name="client_name" placeholder="Кофейня «Утро»" required class="field-input">
            </label>
            <label class="field-label">Телефон или email
                <input type="text" name="contact" placeholder="+7 999 000-00-00" required class="field-input">
            </label>
            <label class="field-label">Площадка
                <select name="platform_name" class="field-input">
                    <option value="Яндекс.Директ">Яндекс.Директ</option>
                    <option value="ВКонтакте">ВКонтакте</option>
                    <option value="Telegram">Telegram</option>
                </select>
            </label>
            <label class="field-label">Плановый бюджет (₽)
                <input type="number" name="budget_estimate" value="50000" min="0" required class="field-input">
            </label>
            <button type="submit" class="btn-dark">Создать заявку</button>
        </form>
    </div>
</div>

<script>
    const leadModal = document.getElementById('leadModal');
    const openLeadBtn = document.getElementById('openLeadModal');
    const closeLeadBtn = document.getElementById('closeLeadModal');

    if (openLeadBtn && leadModal) {
        openLeadBtn.addEventListener('click', () => leadModal.classList.add('is-open'));
    }
    if (closeLeadBtn && leadModal) {
        closeLeadBtn.addEventListener('click', () => leadModal.classList.remove('is-open'));
    }
    if (leadModal) {
        leadModal.addEventListener('click', (e) => {
            if (e.target === leadModal) leadModal.classList.remove('is-open');
        });
    }
</script>
</body>
</html>