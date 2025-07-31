<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryPackage extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'base_salary' => 'integer',
        'bpjs_reduction' => 'integer',
        'pph21_reduction' => 'integer',
        'jamsostek_reduction' => 'integer',
        'calculation_period_start' => 'date',
        'calculation_period_end' => 'date',
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

        foreach ($this->overtimes as $overtime) {
            $totalAmount += $overtime->calculateOvertimePayment();
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
        $baseSalary = (float) $this->base_salary;
        $fixedAmount = $this->getTotalFixedAmount();
        $relativeAmount = $this->getTotalRelativeAmount();
        $overtimeAmount = $this->getTotalOvertimeAmount();
        $weekendBonus = $this->getWeekendOvertimeMealTransportBonus();
        $totalReductions = $this->getTotalReductions();
        $total = $baseSalary + $fixedAmount + $relativeAmount + $overtimeAmount + $weekendBonus - $totalReductions;

        return [
            'base_salary' => $baseSalary,
            'fixed_components' => $fixedAmount,
            'relative_components' => $relativeAmount,
            'overtime_amount' => $overtimeAmount,
            'weekend_overtime_bonus' => $weekendBonus,
            'total_reductions' => $totalReductions,
            'bpjstk_reduction' => $this->bpjstk_reduction,
            'pph21_reduction' => $this->pph21_reduction,
            'jamsostek_reduction' => $this->jamsostek_reduction,
            'meal_budget_cuts' => $this->getMealBudgetCuts(),
            'transport_budget_cuts' => $this->getTransportBudgetCuts(),
            'total_salary' => $total,
            'work_days' => $this->getWorkDaysInPeriod(),
        ];
    }

    /**
     * Get daily meal budget from salary components
     */
    public function getDailyMealBudget(): float
    {
        $mealComponent = $this->salaryComponents()
            ->where('name', 'LIKE', '%meal%')
            ->orWhere('name', 'LIKE', '%makan%')
            ->first();
            
        if (!$mealComponent) {
            return 0;
        }
        
        // Return the component amount directly as daily rate
        return $mealComponent->amount;
    }

    /**
     * Get daily transport budget from salary components
     */
    public function getDailyTransportBudget(): float
    {
        $transportComponent = $this->salaryComponents()
            ->where('name', 'LIKE', '%transport%')
            ->orWhere('name', 'LIKE', '%transportasi%')
            ->first();
            
        if (!$transportComponent) {
            return 0;
        }
        
        // Return the component amount directly as daily rate
        return $transportComponent->amount;
    }

    /**
     * Calculate meal budget cuts based on attendance
     */
    public function getMealBudgetCuts(): float
    {
        $dailyMealBudget = $this->getDailyMealBudget();
        if ($dailyMealBudget <= 0) {
            return 0;
        }
        
        // Meal budget is cut for sick days, break days, and late days
        $totalCutDays = $this->sick_days + $this->break_days + $this->late_days;
        return $totalCutDays * $dailyMealBudget;
    }

    /**
     * Calculate transport budget cuts based on attendance
     */
    public function getTransportBudgetCuts(): float
    {
        $dailyTransportBudget = $this->getDailyTransportBudget();
        if ($dailyTransportBudget <= 0) {
            return 0;
        }
        
        // Transport budget is only cut for sick days and break days (not late days)
        $totalCutDays = $this->sick_days + $this->break_days;
        return $totalCutDays * $dailyTransportBudget;
    }

    /**
     * Calculate total attendance-based budget cuts
     */
    public function getTotalBudgetCuts(): float
    {
        return $this->getMealBudgetCuts() + $this->getTransportBudgetCuts();
    }

    /**
     * Calculate total reductions (BPJS, PPh21, Jamsostek, and budget cuts)
     */
    public function getTotalReductions(): float
    {
        return $this->bpjs_reduction + $this->pph21_reduction + $this->jamsostek_reduction + $this->getTotalBudgetCuts();
    }
}
