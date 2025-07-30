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

        $budgetTotalAmount = $latestBudget->amount; // Total budget amount
        $totalAllocatedToPockets = $budgetPockets->sum('allocated_amount'); // Total allocated to pockets
        $totalBalance = $budgetPockets->sum(fn($bp) => $bp->balance());
        $totalSpent = $totalAllocatedToPockets - $totalBalance;
        $spentPercentage = $totalAllocatedToPockets > 0 ? ($totalSpent / $totalAllocatedToPockets) * 100 : 0;
        $unallocatedBudget = $budgetTotalAmount - $totalAllocatedToPockets; // Remaining unallocated budget

        return [
            Stat::make('Latest Budget', 'IDR ' . number_format($budgetTotalAmount, 0, ',', '.'))
                ->description($latestBudget->name)
                ->descriptionIcon('heroicon-o-wallet')
                ->color('info'),

            Stat::make('Allocated to Pockets', 'IDR ' . number_format($totalAllocatedToPockets, 0, ',', '.'))
                ->description('Total budget allocated to pockets')
                ->descriptionIcon('heroicon-o-squares-2x2')
                ->color('primary'),
                
            Stat::make('Unallocated Budget', 'IDR ' . number_format($unallocatedBudget, 0, ',', '.'))
            ->description($unallocatedBudget >= 0 ? 'Available to allocate' : 'Over-allocated')
            ->descriptionIcon($unallocatedBudget >= 0 ? 'heroicon-o-banknotes' : 'heroicon-o-exclamation-triangle')
            ->color($unallocatedBudget >= 0 ? 'success' : 'warning'),

            Stat::make('Total Spent', 'IDR ' . number_format($totalSpent, 0, ',', '.'))
                ->description(number_format($spentPercentage, 1) . '% of allocated budget')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color($spentPercentage > 100 ? 'danger' : ($spentPercentage > 80 ? 'warning' : 'success')),
        ];
    }
}
