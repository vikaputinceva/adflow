<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CampaignTask extends Model
{
    // Кому адресована задача (executor оставлен для старых записей)
    public const ROLES = [
        'manager' => 'Менеджер',
        'designer' => 'Дизайнер',
        'copywriter' => 'Копирайтер',
        'executor' => 'Исполнитель',
    ];

    protected $fillable = [
        'campaign_id',
        'user_id',
        'title',
        'assignee_role',
        'due_date',
        'is_done',
    ];

    protected $casts = [
        'is_done' => 'boolean',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRoleLabelAttribute()
    {
        return self::ROLES[$this->assignee_role] ?? 'Исполнитель';
    }

    public function getDueLabelAttribute()
    {
        return $this->due_date ? Carbon::parse($this->due_date)->format('d.m.Y') : 'Без срока';
    }

    // Исполнитель видит свои задачи и общие задачи для дизайнеров и копирайтеров.
    // Админ и менеджер видят все.
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->role !== 'executor') {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere(function ($q2) {
                  $q2->whereNull('user_id')
                     ->whereIn('assignee_role', ['designer', 'copywriter', 'executor']);
              });
        });
    }

    // Текст уведомления, если задача просрочена или срок через 3 дня и меньше
    public function deadlineMessage()
    {
        if ($this->is_done || !$this->due_date) {
            return null;
        }

        $due = Carbon::parse($this->due_date)->startOfDay();
        $days = Carbon::today()->diffInDays($due, false);

        if ($days < 0) {
            return "Просрочена задача «{$this->title}» (срок был " . $due->format('d.m.Y') . ").";
        }

        if ($days <= 3) {
            return "Приближается срок задачи «{$this->title}» (" . $due->format('d.m.Y') . ").";
        }

        return null;
    }
}
