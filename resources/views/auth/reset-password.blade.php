<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdFlow — Новый пароль</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Unbounded:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="container">
    <div class="login-layout">
        <section class="login-hero">
            <p class="section-label">Marketing</p>
            <div>
                <h1 class="login-hero-title">Новый пароль</h1>
                <p class="login-hero-text">Придумайте пароль не короче 6 символов и повторите его.</p>
            </div>
            <p class="login-watermark">ADFLOW</p>
        </section>

        <section class="login-card">
            <div class="logo-box">
                <div class="logo-badge"></div>
                <p class="logo-title">AdFlow</p>
            </div>

            <h2 class="login-title">Сброс пароля</h2>

            @if($errors->any())
                <div class="error-box">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="/reset-password" class="login-form">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <label class="field-label">Email
                    <input type="email" name="email" value="{{ old('email', $email) }}" required class="field-input">
                </label>
                <label class="field-label">Новый пароль
                    <input type="password" name="password" required class="field-input">
                </label>
                <label class="field-label">Повторите пароль
                    <input type="password" name="password_confirmation" required class="field-input">
                </label>
                <button type="submit" class="btn-dark btn-full">Сохранить пароль</button>
            </form>
        </section>
    </div>
</div>
</body>
</html>
