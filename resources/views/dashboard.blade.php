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

        @include('partials.sidebar', ['active' => 'home'])

        <main class="content">
            <header class="topbar">
                <p class="section-label">ГЛАВНАЯ</p>
                <div class="topbar-controls">
                    <form method="GET" action="/" style="display: flex; gap: 8px; align-items: center;">
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
                        <span class="pill-tag">ROI · все кампании</span>
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
                        <span class="pill-tag">Общий бюджет</span>
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
                    <span class="mini-pill">+{{ $newLeadsWeek }} за неделю</span>
                </div>

                <div class="stat-box">
                    <p class="stat-box-title">Открытых задач</p>
                    <p class="stat-box-num">{{ $openTasksCount }}</p>
                    <span class="mini-pill">{{ $urgentTasksCount }} горят</span>
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
                        <span class="camp-date">₽</span>
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

                    @forelse($alerts as $alert)
                        <span class="dark-alert-badge">{{ $alert['type'] }}</span>
                        <p class="dark-alert-text">{{ $alert['text'] }}</p>
                    @empty
                        <p class="dark-alert-text" style="margin-bottom: 0;">Лимиты и сроки в порядке, уведомлений нет.</p>
                    @endforelse
                </div>
            </section>

            <section class="two-col-panel">
                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Мои задачи</h2>
                        <a href="/tasks" class="camp-date">Главная → Мои задачи → Детали</a>
                    </div>

                    @forelse($tasks as $task)
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
                    @empty
                        <p class="page-desc">Открытых задач нет.</p>
                    @endforelse
                </div>

                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Интеграции</h2>
                    </div>

                    <div class="integration-box">
                        <p class="camp-title">● Яндекс.Директ API</p>
                        <p class="camp-date">Планируется · REST / JSON</p>
                    </div>

                    <div class="integration-box">
                        <p class="camp-title">● ВКонтакте API</p>
                        <p class="camp-date">Планируется · REST / JSON</p>
                    </div>

                    <div class="integration-box">
                        <p class="camp-title">● Telegram-уведомления</p>
                        <p class="camp-date">Планируется · сроки задач и лимиты бюджета</p>
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