<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Budget;
use App\Models\BudgetPocket;
use Filament\Widgets\ChartWidget;

final class BudgetAllocationChart extends ChartWidget
{
    protected static ?string $heading = 'Budget Allocation Distribution';
    
    protected static ?int $sort = 3;

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
        $data = [];
        $backgroundColors = [
            'rgba(59, 130, 246, 0.8)',   // Blue
            'rgba(34, 197, 94, 0.8)',    // Green
            'rgba(239, 68, 68, 0.8)',    // Red
            'rgba(245, 158, 11, 0.8)',   // Yellow
            'rgba(147, 51, 234, 0.8)',   // Purple
            'rgba(236, 72, 153, 0.8)',   // Pink
            'rgba(14, 165, 233, 0.8)',   // Light Blue
            'rgba(34, 197, 94, 0.8)',    // Emerald
        ];

        foreach ($budgetPockets as $index => $budgetPocket) {
            $labels[] = $budgetPocket->pocket->name;
            $data[] = $budgetPocket->allocated_amount;
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($backgroundColors, 0, count($data)),
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { 
                            const label = context.label || "";
                            const value = "$" + context.parsed.toFixed(2);
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return label + ": " + value + " (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
