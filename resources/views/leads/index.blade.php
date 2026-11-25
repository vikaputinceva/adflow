<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdFlow — Заявки</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Unbounded:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="container">
    <div class="page-layout">
        @include('partials.sidebar', ['active' => 'leads'])

        <main class="content">
            <header class="topbar">
                <p class="section-label">ЗАЯВКИ</p>
                <div class="topbar-controls">
                    <form method="GET" action="/leads" style="display: flex; gap: 8px; align-items: center;">
                        <input type="hidden" name="status" value="{{ $currentStatus }}">
                        <input type="search"
                               name="search"
                               value="{{ $search }}"
                               placeholder="Поиск по клиенту или контакту"
                               class="search-input">
                    </form>
                </div>
            </header>

            <div class="page-head">
                <div>
                    <h1 class="page-title">Входящие заявки</h1>
                    <p class="page-desc">Регистрация обращений клиентов и превращение их в рекламные кампании.</p>
                </div>

                <div class="filter-pills">
                    <a href="/leads?status=all{{ $search ? '&search=' . urlencode($search) : '' }}"
                       class="filter-pill {{ $currentStatus === 'all' ? 'active' : '' }}">Все</a>
                    @foreach($statuses as $key => $label)
                        <a href="/leads?status={{ $key }}{{ $search ? '&search=' . urlencode($search) : '' }}"
                           class="filter-pill {{ $currentStatus === $key ? 'active' : '' }}">{{ $label }}</a>
                    @endforeach
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

            <div class="card">
                <h2 class="card-title mb-14">Новая заявка</h2>
                <form method="POST" action="/leads" style="display: grid; grid-template-columns: 1.2fr 1.2fr 1fr 1fr auto; gap: 10px; align-items: end;">
                    @csrf
                    <label class="field-label">Клиент / проект
                        <input type="text" name="client_name" value="{{ old('client_name') }}" placeholder="Кофейня «Утро»" required class="field-input">
                    </label>
                    <label class="field-label">Телефон или email
                        <input type="text" name="contact" value="{{ old('contact') }}" placeholder="+7 999 000-00-00" required class="field-input">
                    </label>
                    <label class="field-label">Площадка
                        <select name="platform_name" class="field-input">
                            @foreach($platforms as $platform)
                                <option value="{{ $platform->name }}" {{ old('platform_name') === $platform->name ? 'selected' : '' }}>{{ $platform->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="field-label">Бюджет (₽)
                        <input type="number" name="budget_estimate" value="{{ old('budget_estimate', 50000) }}" min="0" required class="field-input">
                    </label>
                    <button type="submit" class="btn-dark">Добавить</button>
                </form>
            </div>

            <section class="tasks-board-wrap">
                @forelse($leads as $lead)
                    <div class="task-card-row {{ $lead->status === 'done' ? 'is-done' : '' }}">
                        <div class="task-card-main">
                            <div>
                                <p class="task-card-title">{{ $lead->client_name }}</p>
                                <p class="task-card-sub">
                                    Контакт: {{ $lead->contact }} · Площадка: {{ $lead->platform_name }} ·
                                    Бюджет: {{ number_format($lead->budget_estimate, 0, ',', ' ') }} ₽ ·
                                    {{ $lead->created_at->format('d.m.Y') }}
                                </p>
                            </div>
                        </div>

                        <div class="task-card-right">
                            <form method="POST" action="/leads/{{ $lead->id }}/status" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="field-input" style="padding: 4px 8px;" onchange="this.form.submit()">
                                    @foreach($statuses as $key => $label)
                                        <option value="{{ $key }}" {{ $lead->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>

                            @if($lead->campaign_id)
                                <a href="/campaigns?selected={{ $lead->campaign_id }}" class="btn-outline" style="padding: 4px 10px;">Кампания →</a>
                            @else
                                <form method="POST" action="/leads/{{ $lead->id }}/convert" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn-outline" style="padding: 4px 10px;">В кампанию</button>
                                </form>
                            @endif

                            <form method="POST" action="/leads/{{ $lead->id }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-outline" style="padding: 4px 10px;" title="Удалить заявку">×</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="task-card-row">
                        <p class="task-card-sub">Заявок по выбранному фильтру пока нет.</p>
                    </div>
                @endforelse
            </section>
        </main>
    </div>
</div>
</body>
</html>
