<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdFlow — Пользователи</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Unbounded:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="container">
    <div class="page-layout">
        @include('partials.sidebar', ['active' => 'users'])

        <main class="content">
            <header class="topbar">
                <p class="section-label">ПОЛЬЗОВАТЕЛИ</p>
            </header>

            <div class="page-head">
                <div>
                    <h1 class="page-title">Пользователи системы</h1>
                    <p class="page-desc">Учётные записи создаёт администратор и выдаёт им роль.</p>
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
                <h2 class="card-title mb-14">Новый пользователь</h2>
                <form method="POST" action="/users" style="display: grid; grid-template-columns: 1.2fr 1.2fr 1fr 1fr 1fr auto; gap: 10px; align-items: end;">
                    @csrf
                    <label class="field-label">ФИО
                        <input type="text" name="name" value="{{ old('name') }}" required class="field-input">
                    </label>
                    <label class="field-label">Email
                        <input type="email" name="email" value="{{ old('email') }}" required class="field-input">
                    </label>
                    <label class="field-label">Телефон
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+7 999 000-00-00" class="field-input">
                    </label>
                    <label class="field-label">Роль
                        <select name="role" class="field-input">
                            @foreach($roles as $key => $label)
                                <option value="{{ $key }}" {{ old('role', 'executor') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="field-label">Пароль
                        <input type="password" name="password" required class="field-input">
                    </label>
                    <button type="submit" class="btn-dark">Создать</button>
                </form>
            </div>

            <section class="tasks-board-wrap">
                @foreach($users as $item)
                    <div class="task-card-row">
                        <div class="task-card-main">
                            <div>
                                <p class="task-card-title">{{ $item->name }}</p>
                                <p class="task-card-sub">{{ $item->email }} · {{ $item->phone ?: 'телефон не указан' }}</p>
                            </div>
                        </div>

                        <div class="task-card-right">
                            <span class="task-role-badge">{{ $item->role_label }}</span>
                            @if($item->id !== auth()->id())
                                <form method="POST" action="/users/{{ $item->id }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-outline" style="padding: 4px 10px;" title="Удалить пользователя">×</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </section>
        </main>
    </div>
</div>
</body>
</html>
