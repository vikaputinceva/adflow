<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdFlow — Вход</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Unbounded:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="container">
    <div class="login-layout">
        <section class="login-hero">
            <p class="section-label">Marketing</p>
            <div>
                <h1 class="login-hero-title">Все рекламные кампании под контролем</h1>
                <p class="login-hero-text">Планирование, задачи, бюджеты и окупаемость малого бизнеса в одной системе.</p>
                <div class="filter-pills">
                    <span class="filter-pill active">Планирование</span>
                    <span class="filter-pill">Бюджеты</span>
                    <span class="filter-pill">ROI и KPI</span>
                </div>
            </div>
            <p class="login-watermark">ADFLOW</p>
        </section>

        <section class="login-card">
            <div class="logo-box">
                <div class="logo-badge"></div>
                <p class="logo-title">AdFlow</p>
            </div>

            <div>
                <h2 class="login-title">Вход в систему</h2>
                <p class="page-desc">Учётные записи создаёт администратор — войдите под своей учётной записью.</p>
            </div>

            @if($errors->any())
                <div class="error-box">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="login-form">
                @csrf
                <label class="field-label">Email
                    <input type="email" name="email" value="{{ old('email', 'admin@adflow.ru') }}" required class="field-input">
                </label>
                <label class="field-label">Пароль
                    <input type="password" name="password" value="admin123" required class="field-input">
                </label>
                <button type="submit" class="btn-dark btn-full">Войти</button>
            </form>

            <div class="roles-box">
                <p class="roles-title">Роли в системе</p>
                <div class="filter-pills">
                    <span class="role-tag">Администратор</span>
                    <span class="role-tag">Менеджер</span>
                </div>
            </div>
        </section>
    </div>
</div>
</body>
</html>