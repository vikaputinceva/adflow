<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignTask extends Model
{
    protected $fillable = [
        'campaign_id',
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
}