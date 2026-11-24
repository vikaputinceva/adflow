<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdFlow — Восстановление пароля</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Unbounded:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="container">
    <div class="login-layout">
        <section class="login-hero">
            <p class="section-label">Marketing</p>
            <div>
                <h1 class="login-hero-title">Восстановление доступа</h1>
                <p class="login-hero-text">Укажите email своей учётной записи, мы отправим ссылку для создания нового пароля.</p>
            </div>
            <p class="login-watermark">ADFLOW</p>
        </section>

        <section class="login-card">
            <div class="logo-box">
                <div class="logo-badge"></div>
                <p class="logo-title">AdFlow</p>
            </div>

            <h2 class="login-title">Сброс пароля</h2>

            @if(session('success'))
                <div class="card" style="padding: 12px 16px; font-weight: 600;">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="error-box">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="/forgot-password" class="login-form">
                @csrf
                <label class="field-label">Email
                    <input type="email" name="email" value="{{ old('email') }}" required class="field-input">
                </label>
                <button type="submit" class="btn-dark btn-full">Отправить ссылку</button>
                <a href="/login" class="page-desc" style="text-align: center;">Вернуться ко входу</a>
            </form>
        </section>
    </div>
</div>
</body>
</html>
