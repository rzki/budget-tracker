<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Overtime extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'hours' => 'decimal:2',
        'overtime_date' => 'date',
    ];

    public function salaryPackage(): BelongsTo
    {
        return $this->belongsTo(SalaryPackage::class);
    }

    /**
     * Check if overtime is weekday type
     */
    public function isWeekday(): bool
    {
        return $this->type === 'weekday';
    }

    /**
     * Check if overtime is weekend type
     */
    public function isWeekend(): bool
    {
        return $this->type === 'weekend';
    }

    /**
     * Get the hourly rate for overtime calculation from the associated salary package
     */
    public function getOvertimeHourlyRate(): float
    {
        return $this->salaryPackage->getOvertimeHourlyRate();
    }

    /**
     * Calculate overtime payment based on Indonesian Disnaker rules
     */
    public function calculateOvertimePayment(): float
    {
        $basePay = $this->getOvertimeHourlyRate();

        if ($this->isWeekday()) {
            // Weekday overtime: 1.5x for first 1 hour, 2x for subsequent hours
            $firstHour = min($this->hours, 1);
            $remainingHours = max($this->hours - 1, 0);
            
            return ($firstHour * $basePay * 1.5) + ($remainingHours * $basePay * 2);
        } else {
            // Weekend overtime: 2x for first 7 hours, 3x for 8th hour, 4x for subsequent hours
            $firstSevenHours = min($this->hours, 7);
            $eighthHour = min(max($this->hours - 7, 0), 1);
            $remainingHours = max($this->hours - 8, 0);
            
            return ($firstSevenHours * $basePay * 2) + 
                   ($eighthHour * $basePay * 3) + 
                   ($remainingHours * $basePay * 4);
        }
    }

    /**
     * Get overtime multiplier description for display
     */
    public function getOvertimeRulesDescription(): string
    {
        if ($this->isWeekday()) {
            return 'Weekday: 1.5x (1st hour), 2x (additional hours)';
        }
        
        return 'Weekend: 2x (1-7 hours), 3x (8th hour), 4x (9+ hours)';
    }
}
