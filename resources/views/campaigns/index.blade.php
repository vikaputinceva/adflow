<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdFlow — Кампании</title>
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
                <a href="/campaigns" class="nav-link active">Кампании</a>
                <a href="/tasks" class="nav-link">Задачи</a>
                <a href="/finance" class="nav-link">Финансы</a>
                <a href="/reports" class="nav-link">Отчёты</a>
                <a href="/login" class="nav-link">Вход</a>
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
                <p class="section-label">Кампании</p>
                <div class="topbar-controls">
                    <form method="GET" action="/campaigns" style="display: flex; gap: 8px; align-items: center;">
                        <input type="hidden" name="status" value="{{ $currentStatus }}">
                        <input type="search"
                               name="search"
                               value="{{ $search ?? '' }}"
                               placeholder="Поиск по кампаниям"
                               class="search-input">
                        <button type="submit" class="btn-outline">Найти</button>
                    </form>
                    <button type="button" id="openCampaignModal" class="btn-dark">+ Новая кампания</button>
                </div>
            </header>

            <div class="page-head">
                <div>
                    <h1 class="page-title">Реестр кампаний</h1>
                    <p class="page-desc">Площадки, бюджеты и окупаемость каждой кампании.</p>
                </div>

                @php $searchParam = !empty($search) ? '&search=' . urlencode($search) : ''; @endphp
                <div class="filter-pills">
                    <a href="/campaigns?status=all{{ $searchParam }}" class="filter-pill {{ $currentStatus == 'all' ? 'active' : '' }}">Все</a>
                    <a href="/campaigns?status=active{{ $searchParam }}" class="filter-pill {{ $currentStatus == 'active' ? 'active' : '' }}">Активные</a>
                    <a href="/campaigns?status=paused{{ $searchParam }}" class="filter-pill {{ $currentStatus == 'paused' ? 'active' : '' }}">Пауза</a>
                    <a href="/campaigns?status=completed{{ $searchParam }}" class="filter-pill {{ $currentStatus == 'completed' ? 'active' : '' }}">Завершены</a>
                </div>
            </div>

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

            <section class="campaigns-grid">
                <div class="card table-card">
                    <table class="campaigns-table">
                        <thead>
                            <tr>
                                <th>Кампания</th>
                                <th>Площадка</th>
                                <th>Статус</th>
                                <th>Бюджет</th>
                                <th class="text-right">ROI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaigns as $campaign)
                                <tr class="{{ $selectedCampaign && $selectedCampaign->id == $campaign->id ? 'is-selected' : '' }}">
                                    <td>
                                        <a href="/campaigns?status={{ $currentStatus }}&selected={{ $campaign->id }}{{ $searchParam }}">
                                            <p class="camp-title">{{ $campaign->name }}</p>
                                            <p class="camp-date">до {{ date('d.m.Y', strtotime($campaign->end_date)) }}</p>
                                        </a>
                                    </td>
                                    <td>{{ $campaign->platform_name }}</td>
                                    <td>
                                        @if($campaign->status == 'active')
                                            <span class="badge badge-active">Активна</span>
                                        @elseif($campaign->status == 'paused')
                                            <span class="badge badge-warn">Пауза</span>
                                        @else
                                            <span class="badge badge-done">Завершена</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="mini-bar">
                                            <div class="mini-bar-fill {{ $campaign->status == 'completed' ? 'done' : ($campaign->spent_percent >= 85 ? 'warn' : '') }}"
                                                 style="width: {{ $campaign->spent_percent }}%;"></div>
                                        </div>
                                        <p class="mini-sub">{{ number_format($campaign->budget_spent, 0, ',', ' ') }} ₽ из {{ number_format($campaign->budget_total, 0, ',', ' ') }} ₽</p>
                                    </td>
                                    <td class="text-right"><strong>{{ $campaign->roi }}%</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="padding: 24px 8px; text-align: center;">
                                        По запросу ничего не найдено.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <aside class="side-column">
                    @if($selectedCampaign)
                        <div class="selected-banner">
                            <span class="selected-tag">Выбрана кампания</span>
                            <h2 class="selected-name">{{ $selectedCampaign->name }}</h2>
                            <div class="selected-pills">
                                <span class="selected-pill">ROI {{ $selectedCampaign->roi }}%</span>
                                <span class="selected-pill">{{ $selectedCampaign->spent_percent }}%</span>
                            </div>
                            <p class="selected-note">{{ $selectedCampaign->platform_name }} · потрачено {{ number_format($selectedCampaign->budget_spent, 0, ',', ' ') }} ₽ · выручка {{ number_format($selectedCampaign->revenue_attributed, 0, ',', ' ') }} ₽</p>

                            <form method="POST" action="/campaigns/{{ $selectedCampaign->id }}" style="margin-top: 14px;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-outline">Удалить кампанию</button>
                            </form>
                        </div>

                        <form method="POST" action="/campaigns/{{ $selectedCampaign->id }}/status" class="card" style="padding: 16px 20px; display: flex; gap: 10px; align-items: center;">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="field-input" style="flex: 1;">
                                <option value="active" {{ $selectedCampaign->status == 'active' ? 'selected' : '' }}>Активна</option>
                                <option value="paused" {{ $selectedCampaign->status == 'paused' ? 'selected' : '' }}>Пауза</option>
                                <option value="completed" {{ $selectedCampaign->status == 'completed' ? 'selected' : '' }}>Завершена</option>
                            </select>
                            <button type="submit" class="btn-dark">Обновить</button>
                        </form>

                        <div class="card">
                            <h2 class="card-title mb-14">Расход и выручка</h2>
                            <form method="POST" action="/campaigns/{{ $selectedCampaign->id }}/metrics" class="create-form">
                                @csrf
                                @method('PATCH')
                                <label class="field-label">Потрачено (₽)
                                    <input type="number"
                                           name="budget_spent"
                                           value="{{ old('budget_spent', $selectedCampaign->budget_spent) }}"
                                           min="0"
                                           step="0.01"
                                           required
                                           class="field-input">
                                </label>
                                <label class="field-label">Выручка (₽)
                                    <input type="number"
                                           name="revenue_attributed"
                                           value="{{ old('revenue_attributed', $selectedCampaign->revenue_attributed) }}"
                                           min="0"
                                           step="0.01"
                                           required
                                           class="field-input">
                                </label>
                                <button type="submit" class="btn-dark">Сохранить показатели</button>
                            </form>
                        </div>

                        <div class="card">
                            <div class="card-head">
                                <h2 class="card-title">Материалы</h2>
                            </div>

                            <form method="POST" action="/campaigns/{{ $selectedCampaign->id }}/materials" class="create-form" style="margin-bottom: 14px;">
                                @csrf
                                <input type="text" name="title" placeholder="Название материала (например, Баннер ВК)" required class="field-input">
                                <input type="text" name="format_label" placeholder="Формат (например, 1080×1080)" required class="field-input">
                                <button type="submit" class="btn-outline">Прикрепить</button>
                            </form>

                            <div class="materials-grid">
                                @forelse($materials as $material)
                                    <div>
                                        <div class="material-box accent">{{ $material->format_label }}</div>
                                        <p class="material-label">{{ $material->title }}</p>
                                    </div>
                                @empty
                                    <div>
                                        <div class="material-box accent">1200×628</div>
                                        <p class="material-label">Баннер 1</p>
                                    </div>
                                    <div>
                                        <div class="material-box">Объявление</div>
                                        <p class="material-label">Текст объявления</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </aside>
            </section>
        </main>

    </div>
</div>

<div class="modal-backdrop {{ $errors->any() ? 'is-open' : '' }}" id="campaignModal">
    <div class="modal-window">
        <div class="modal-head">
            <h2 class="card-title">Новая кампания</h2>
            <button type="button" class="modal-close" id="closeCampaignModal" aria-label="Закрыть">&times;</button>
        </div>

        <form method="POST" action="/campaigns" class="create-form">
            @csrf
            <label class="field-label">Название
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Весенняя распродажа" required class="field-input">
            </label>
            <label class="field-label">Площадка
                <select name="platform_name" class="field-input">
                    <option value="Яндекс.Директ" {{ old('platform_name') == 'Яндекс.Директ' ? 'selected' : '' }}>Яндекс.Директ</option>
                    <option value="ВКонтакте" {{ old('platform_name') == 'ВКонтакте' ? 'selected' : '' }}>ВКонтакте</option>
                    <option value="Telegram" {{ old('platform_name') == 'Telegram' ? 'selected' : '' }}>Telegram</option>
                </select>
            </label>
            <label class="field-label">Бюджет (₽)
                <input type="number" name="budget_total" value="{{ old('budget_total') }}" placeholder="100000" min="1" required class="field-input">
            </label>
            <label class="field-label">Дата окончания
                <input type="date" name="end_date" value="{{ old('end_date') }}" required class="field-input">
            </label>
            <button type="submit" class="btn-dark">Сохранить</button>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('campaignModal');
    const openBtn = document.getElementById('openCampaignModal');
    const closeBtn = document.getElementById('closeCampaignModal');

    if (openBtn && modal) {
        openBtn.addEventListener('click', () => modal.classList.add('is-open'));
    }

    if (closeBtn && modal) {
        closeBtn.addEventListener('click', () => modal.classList.remove('is-open'));
    }

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.remove('is-open');
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal) modal.classList.remove('is-open');
    });
</script>
</body>
</html>