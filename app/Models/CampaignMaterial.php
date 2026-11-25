<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignMaterial extends Model
{
    protected $fillable = [
        'campaign_id',
        'title',
        'format_label',
        'file_path',
        'file_name',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    // Ссылка на загруженный файл (файлы лежат в storage/app/public)
    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }
}
