<?php

// Сообщения об ошибках валидации на русском
return [
    'required' => 'Поле «:attribute» обязательно для заполнения.',
    'email' => 'Поле «:attribute» должно быть корректным email-адресом.',
    'string' => 'Поле «:attribute» должно быть строкой.',
    'numeric' => 'Поле «:attribute» должно быть числом.',
    'date' => 'Поле «:attribute» должно быть корректной датой.',
    'in' => 'Выбрано некорректное значение поля «:attribute».',
    'exists' => 'Выбрано некорректное значение поля «:attribute».',
    'unique' => 'Такое значение поля «:attribute» уже занято.',
    'confirmed' => 'Подтверждение поля «:attribute» не совпадает.',
    'max' => [
        'numeric' => 'Поле «:attribute» не может быть больше :max.',
        'string' => 'Поле «:attribute» не может быть длиннее :max символов.',
    ],
    'min' => [
        'numeric' => 'Поле «:attribute» должно быть не меньше :min.',
        'string' => 'Поле «:attribute» должно быть не короче :min символов.',
    ],

    'attributes' => [
        'name' => 'Название',
        'email' => 'Email',
        'password' => 'Пароль',
        'phone' => 'Телефон',
        'role' => 'Роль',
        'title' => 'Название',
        'status' => 'Статус',
        'platform_name' => 'Площадка',
        'budget_total' => 'Бюджет',
        'budget_spent' => 'Расход',
        'revenue_attributed' => 'Выручка',
        'end_date' => 'Дата окончания',
        'due_date' => 'Срок',
        'campaign_id' => 'Кампания',
        'user_id' => 'Исполнитель',
        'assignee_role' => 'Роль исполнителя',
        'format_label' => 'Формат',
        'client_name' => 'Клиент',
        'contact' => 'Контакт',
        'budget_estimate' => 'Плановый бюджет',
    ],
];
