<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = [
        'admin' => 'Администратор',
        'manager' => 'Менеджер',
        'executor' => 'Исполнитель',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getRoleLabelAttribute()
    {
        return self::ROLES[$this->role] ?? 'Исполнитель';
    }

    // Две буквы для аватарки: первые буквы имени и фамилии
    public function getInitialsAttribute()
    {
        $words = explode(' ', trim($this->name));
        $first = mb_substr($words[0], 0, 1);
        $second = mb_substr($words[1] ?? $words[0], 0, 1);

        return mb_strtoupper($first . $second);
    }
}
