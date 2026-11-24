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

    public function tasks()
    {
        return $this->hasMany(CampaignTask::class);
    }

    // Сколько процентов бюджета потрачено
    public function getSpentPercentAttribute()
    {
        if ($this->budget_total <= 0) {
            return 0;
        }

        return round($this->budget_spent / $this->budget_total * 100);
    }

    public function getRemainingBudgetAttribute()
    {
        return max(0, $this->budget_total - $this->budget_spent);
    }

    public function getProfitAttribute()
    {
        return $this->revenue_attributed - $this->budget_spent;
    }

    // ROI = (выручка - расходы) / расходы * 100
    public function getRoiAttribute()
    {
        if ($this->budget_spent <= 0) {
            return 0;
        }

        return round(($this->revenue_attributed - $this->budget_spent) / $this->budget_spent * 100);
    }

    // Текст уведомления, если бюджет почти закончился (85% и больше)
    public function limitMessage()
    {
        if ($this->spent_percent < 85) {
            return null;
        }

        return "Лимит бюджета кампании «{$this->name}» освоен на {$this->spent_percent}% (остаток "
            . number_format($this->remaining_budget, 0, ',', ' ') . " ₽).";
    }

    // Итоги по всем кампаниям сразу
    public static function totals(): array
    {
        $budget = static::sum('budget_total');
        $spent = static::sum('budget_spent');
        $revenue = static::sum('revenue_attributed');

        $usedPercent = $budget > 0 ? min(100, round($spent / $budget * 100)) : 0;

        return [
            'budget' => $budget,
            'spent' => $spent,
            'revenue' => $revenue,
            'remaining' => max(0, $budget - $spent),
            'profit' => $revenue - $spent,
            'roi' => $spent > 0 ? round(($revenue - $spent) / $spent * 100) : 0,
            'used_percent' => $usedPercent,
            'left_percent' => 100 - $usedPercent,
        ];
    }
}
