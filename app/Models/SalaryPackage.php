<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryPackage extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'calculation_period_start' => 'datetime',
        'calculation_period_end' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function salaryComponents(): HasMany
    {
        return $this->hasMany(SalaryComponent::class);
    }

    public function overtimes(): HasMany
    {
        return $this->hasMany(Overtime::class);
    }

    public function fixedComponents(): HasMany
    {
        return $this->salaryComponents()->where('type', 'fixed')->where('is_active', true);
    }

    public function relativeComponents(): HasMany
    {
        return $this->salaryComponents()->where('type', 'relative')->where('is_active', true);
    }

    /**
     * Calculate total fixed salary components
     */
    public function getTotalFixedAmount(): float
    {
        return $this->fixedComponents()->sum('amount');
    }

    /**
     * Calculate total relative salary components based on work days
     */
    public function getTotalRelativeAmount(): float
    {
        $workDays = $this->getWorkDaysInPeriod();
        return $this->relativeComponents()->get()->sum(function ($component) use ($workDays) {
            return $component->amount * $workDays;
        });
    }

    /**
     * Get work days between calculation period
     */
    public function getWorkDaysInPeriod(): int
    {
        $start = $this->calculation_period_start;
        $end = $this->calculation_period_end;
        
        $workDays = 0;
        $current = $start->copy();
        
        while ($current->lte($end)) {
            if ($current->isWeekday()) {
                $workDays++;
            }
            $current->addDay();
        }
        
        return $workDays;
    }

    /**
     * Calculate hourly rate for overtime calculations only (Disnaker formula)
     * Base salary divided by 173 (standard monthly working hours according to Indonesian labor law)
     */
    public function getOvertimeHourlyRate(): float
    {
        return $this->base_salary > 0 ? (float) ($this->base_salary / 173) : 0;
    }

    /**
     * Calculate total overtime amount using Indonesian Disnaker rules
     */
    public function getTotalOvertimeAmount(): float
    {
        $totalAmount = 0;
        $hourlyRate = $this->getOvertimeHourlyRate();

        foreach ($this->overtimes as $overtime) {
            if ($overtime->type === 'weekday') {
                // Weekday overtime: 1.5x for first 1 hour, 2x for subsequent hours
                $firstHour = min($overtime->hours, 1);
                $remainingHours = max($overtime->hours - 1, 0);
                $totalAmount += ($firstHour * $hourlyRate * 1.5) + ($remainingHours * $hourlyRate * 2);
            } else {
                // Weekend overtime: 2x for first 7 hours, 3x for 8th hour, 4x for subsequent hours
                $firstSevenHours = min($overtime->hours, 7);
                $eighthHour = min(max($overtime->hours - 7, 0), 1);
                $remainingHours = max($overtime->hours - 8, 0);
                
                $totalAmount += ($firstSevenHours * $hourlyRate * 2) + 
                               ($eighthHour * $hourlyRate * 3) + 
                               ($remainingHours * $hourlyRate * 4);
            }
        }

        return $totalAmount;
    }

    /**
     * Calculate additional meal and transport for weekend overtime
     */
    public function getWeekendOvertimeMealTransportBonus(): float
    {
        $weekendOvertimeDays = $this->overtimes()->where('type', 'weekend')->count();
        
        if ($weekendOvertimeDays === 0) {
            return 0;
        }

        $mealComponent = $this->relativeComponents()->where('name', 'Meal')->first();
        $transportComponent = $this->relativeComponents()->where('name', 'Transport')->first();

        $bonus = 0;
        if ($mealComponent) {
            $bonus += $mealComponent->amount * $weekendOvertimeDays;
        }
        if ($transportComponent) {
            $bonus += $transportComponent->amount * $weekendOvertimeDays;
        }

        return $bonus;
    }

    /**
     * Calculate total salary estimation
     */
    public function getTotalSalaryEstimation(): array
    {
        $fixedAmount = $this->getTotalFixedAmount();
        $relativeAmount = $this->getTotalRelativeAmount();
        $overtimeAmount = $this->getTotalOvertimeAmount();
        $weekendBonus = $this->getWeekendOvertimeMealTransportBonus();
        $total = $fixedAmount + $relativeAmount + $overtimeAmount + $weekendBonus;

        return [
            'fixed_components' => $fixedAmount,
            'relative_components' => $relativeAmount,
            'overtime_amount' => $overtimeAmount,
            'weekend_overtime_bonus' => $weekendBonus,
            'total_salary' => $total,
            'work_days' => $this->getWorkDaysInPeriod(),
        ];
    }
}
