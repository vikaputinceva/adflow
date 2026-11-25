<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    protected $fillable = ['name'];

    // Площадку нельзя удалять, пока по ней есть кампании или заявки
    public function isUsed(): bool
    {
        return Campaign::where('platform_name', $this->name)->exists()
            || LeadRequest::where('platform_name', $this->name)->exists();
    }
}
