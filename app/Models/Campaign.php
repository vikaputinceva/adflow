<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'name',
        'platform_name',
        'status',
        'budget_total',
        'budget_spent',
        'revenue_attributed',
        'end_date',
    ];
    public function materials()
    {
        return $this->hasMany(CampaignMaterial::class);
    }
}
