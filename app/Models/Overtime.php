<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Overtime extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
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
     * Calculate total overtime hours from start and end time (including seconds)
     * Automatically subtract 1 hour for weekend overtime (break time)
     */
    public function getTotalHours(): float
    {
        // Extract time from datetime objects with seconds precision
        $startTime = Carbon::createFromFormat('H:i:s', $this->start_time->format('H:i:s'));
        $endTime = Carbon::createFromFormat('H:i:s', $this->end_time->format('H:i:s'));
        
        // If end time is before start time, assume it crosses midnight
        if ($endTime->lt($startTime)) {
            $endTime->addDay();
        }
        
        // Calculate total seconds and convert to hours with seconds precision
        $totalSeconds = $startTime->diffInSeconds($endTime);
        $totalHours = $totalSeconds / 3600; // 3600 seconds in an hour
        
        // Subtract 1 hour for weekend overtime (break time)
        if ($this->isWeekend() && $totalHours > 1) {
            $totalHours -= 1;
        }
        
        return round($totalHours, 4); // Round to 4 decimal places for seconds precision
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
        $hours = $this->getTotalHours();

        // Ensure we have positive values
        if ($basePay <= 0 || $hours <= 0) {
            return 0;
        }

        if ($this->isWeekday()) {
            // Weekday overtime: 1.5x for first 1 hour, 2x for subsequent hours
            $firstHour = min($hours, 1);
            $remainingHours = max($hours - 1, 0);
            
            return ($firstHour * $basePay * 1.5) + ($remainingHours * $basePay * 2);
        } else {
            // Weekend overtime: 2x for first 7 hours, 3x for 8th hour, 4x for subsequent hours
            $firstSevenHours = min($hours, 7);
            $eighthHour = min(max($hours - 7, 0), 1);
            $remainingHours = max($hours - 8, 0);
            
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
