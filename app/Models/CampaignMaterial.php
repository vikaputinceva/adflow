<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignMaterial extends Model
{
    protected $fillable = [
        'campaign_id',
        'title',
        'format_label',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}