@php
    $currentUser = auth()->user();
    $isStaff = in_array($currentUser->role, ['admin', 'manager'], true);
@endphp
<aside class="sidebar">
    <div class="logo-box">
        <div class="logo-badge"></div>
        <div>
            <p class="logo-title">AdFlow</p>
            <p class="logo-sub">Реклама малого бизнеса</p>
        </div>
    </div>

    <nav class="nav-list">
        @if($isStaff)
            <a href="/" class="nav-link {{ $active === 'home' ? 'active' : '' }}" title="Главная">Главная</a>
            <a href="/campaigns" class="nav-link {{ $active === 'campaigns' ? 'active' : '' }}" title="Реестр кампаний">Кампании</a>
        @endif

        <a href="/tasks" class="nav-link {{ $active === 'tasks' ? 'active' : '' }}" title="Задачи и сроки">{{ $isStaff ? 'Задачи' : 'Мои задачи' }}</a>

        @if($isStaff)
            <a href="/leads" class="nav-link {{ $active === 'leads' ? 'active' : '' }}" title="Входящие заявки">Заявки</a>
            <a href="/finance" class="nav-link {{ $active === 'finance' ? 'active' : '' }}" title="Бюджеты и лимиты">Финансы</a>
            <a href="/reports" class="nav-link {{ $active === 'reports' ? 'active' : '' }}" title="Отчёты, KPI и ROI">Отчёты</a>
        @endif

        @if($currentUser->role === 'admin')
            <a href="/platforms" class="nav-link {{ $active === 'platforms' ? 'active' : '' }}" title="Справочник площадок">Площадки</a>
            <a href="/users" class="nav-link {{ $active === 'users' ? 'active' : '' }}" title="Создание пользователей">Пользователи</a>
        @endif
    </nav>

    @if($isStaff)
        <div class="sidebar-limit">
            <p class="sidebar-limit-title">Лимит бюджета</p>
            <p class="sidebar-limit-num">{{ $budgetTotals['left_percent'] }}%</p>
            <div class="progress-track">
                <div class="progress-fill" style="width: {{ $budgetTotals['used_percent'] }}%;"></div>
            </div>
            <p class="sidebar-limit-note">Освоено {{ $budgetTotals['used_percent'] }}% общего бюджета</p>
        </div>
    @endif

    <div class="user-card">
        <div class="user-avatar">{{ $currentUser->initials }}</div>
        <div>
            <p class="user-name">{{ $currentUser->name }}</p>
            <p class="user-role">{{ $currentUser->role_label }}</p>
        </div>
        <form method="POST" action="/logout" style="margin-left: auto;">
            @csrf
            <button type="submit" class="btn-outline" style="padding: 6px 10px;" title="Выйти">→</button>
        </form>
    </div>
</aside>
