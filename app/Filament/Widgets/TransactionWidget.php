<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Budget;
use App\Models\BudgetPocket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class TransactionWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $latestBudget = Budget::latest()->first();
        
        if (!$latestBudget) {
            return [
                Stat::make('No Budget', 'Create a budget to see stats')
                    ->description('Get started with your budget tracking')
                    ->descriptionIcon('heroicon-o-plus-circle')
                    ->color('gray'),
            ];
        }

        $budgetPockets = BudgetPocket::with(['pocket'])
            ->where('budget_id', $latestBudget->id)
            ->get();

        $totalAllocated = $budgetPockets->sum('allocated_amount');
        $totalBalance = $budgetPockets->sum(fn($bp) => $bp->balance());
        $totalSpent = $totalAllocated - $totalBalance;
        $spentPercentage = $totalAllocated > 0 ? ($totalSpent / $totalAllocated) * 100 : 0;

        return [
            Stat::make('Total Allocated', 'IDR ' . number_format($totalAllocated, 0, ',', '.'))
                ->description($latestBudget->name)
                ->descriptionIcon('heroicon-o-wallet')
                ->color('info'),

            Stat::make('Total Balance', 'IDR ' . number_format($totalBalance, 0, ',', '.'))
                ->description($totalBalance < 0 ? 'Overspent' : 'Remaining')
                ->descriptionIcon($totalBalance < 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                ->color($totalBalance < 0 ? 'danger' : 'success'),

            Stat::make('Total Spent', 'IDR ' . number_format($totalSpent, 0, ',', '.'))
                ->description(number_format($spentPercentage, 1) . '% of budget')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color($spentPercentage > 100 ? 'danger' : ($spentPercentage > 80 ? 'warning' : 'success')),

            Stat::make('Active Pockets', $budgetPockets->count())
                ->description('Budget categories')
                ->descriptionIcon('heroicon-o-squares-2x2')
                ->color('primary'),
        ];
    }
}
