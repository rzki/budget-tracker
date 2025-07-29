<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Budget;
use App\Models\BudgetPocket;
use Filament\Widgets\ChartWidget;

final class PocketSpendingChart extends ChartWidget
{
    protected static ?string $heading = 'Pocket Spending Overview';
    
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $latestBudget = Budget::latest()->first();
        
        if (!$latestBudget) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $budgetPockets = BudgetPocket::with(['pocket'])
            ->where('budget_id', $latestBudget->id)
            ->get();

        $labels = [];
        $allocatedData = [];
        $spentData = [];
        $balanceData = [];

        foreach ($budgetPockets as $budgetPocket) {
            $spent = $budgetPocket->transactions()
                ->where('type', 'expense')
                ->sum('amount');
            
            $labels[] = $budgetPocket->pocket->name;
            $allocatedData[] = $budgetPocket->allocated_amount;
            $spentData[] = $spent;
            $balanceData[] = $budgetPocket->balance();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Allocated',
                    'data' => $allocatedData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Spent',
                    'data' => $spentData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Balance',
                    'data' => $balanceData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "$" + value.toFixed(2); }',
                    ],
                ],
            ],
        ];
    }
}
