<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdFlow — Площадки</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Unbounded:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="container">
    <div class="page-layout">
        @include('partials.sidebar', ['active' => 'platforms'])

        <main class="content">
            <header class="topbar">
                <p class="section-label">ПЛОЩАДКИ</p>
            </header>

            <div class="page-head">
                <div>
                    <h1 class="page-title">Рекламные площадки</h1>
                    <p class="page-desc">Справочник площадок. Из него выбирают площадку в кампаниях и заявках.</p>
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
                <h2 class="card-title mb-14">Новая площадка</h2>
                <form method="POST" action="/platforms" style="display: grid; grid-template-columns: 1fr auto; gap: 10px; align-items: end;">
                    @csrf
                    <label class="field-label">Название
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Например, Одноклассники" required class="field-input">
                    </label>
                    <button type="submit" class="btn-dark">Добавить</button>
                </form>
            </div>

            <section class="tasks-board-wrap">
                @foreach($platforms as $platform)
                    <div class="task-card-row">
                        <div class="task-card-main">
                            <div>
                                <p class="task-card-title">{{ $platform->name }}</p>
                                <p class="task-card-sub">Кампаний: {{ $counts[$platform->name] ?? 0 }}</p>
                            </div>
                        </div>

                        <div class="task-card-right">
                            <form method="POST" action="/platforms/{{ $platform->id }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-outline" style="padding: 4px 10px;" title="Удалить площадку">×</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </section>
        </main>
    </div>
</div>
</body>
</html>
