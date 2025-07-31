<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryComponent extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function salaryPackage(): BelongsTo
    {
        return $this->belongsTo(SalaryPackage::class);
    }

    /**
     * Check if component is fixed type
     */
    public function isFixed(): bool
    {
        return $this->type === 'fixed';
    }

    /**
     * Check if component is relative type
     */
    public function isRelative(): bool
    {
        return $this->type === 'relative';
    }

    /**
     * Calculate total amount based on work days (for relative components)
     */
    public function calculateAmount(int $workDays = null): float
    {
        if ($this->isFixed()) {
            return (float) $this->amount;
        }

        if ($workDays === null) {
            $workDays = $this->salaryPackage->getWorkDaysInPeriod();
        }

        return (float) ($this->amount * $workDays);
    }
}
