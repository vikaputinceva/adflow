<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadRequest extends Model
{
    public const STATUSES = [
        'new' => 'Новая',
        'in_work' => 'В работе',
        'done' => 'Закрыта',
    ];

    protected $fillable = [
        'client_name',
        'contact',
        'platform_name',
        'budget_estimate',
        'status',
        'campaign_id',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
