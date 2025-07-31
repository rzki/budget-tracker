<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Models\Budget;
use App\Models\BudgetPocket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class PocketsAllocationWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';
    protected function getStats(): array
    {
        $latestBudget = Budget::latest()->first();
        
        if (!$latestBudget) {
            return [
                Stat::make('No Budget', 'Create a budget to see pocket allocations')
                    ->description('Get started with your budget tracking')
                    ->descriptionIcon('heroicon-o-plus-circle')
                    ->color('gray'),
            ];
        }

        $budgetPockets = BudgetPocket::with(['pocket', 'transactions'])
            ->where('budget_id', $latestBudget->id)
            ->get();

        if ($budgetPockets->isEmpty()) {
            return [
                Stat::make('No Pockets', 'No pocket allocations found')
                    ->description('Add pockets to ' . $latestBudget->name)
                    ->descriptionIcon('heroicon-o-wallet')
                    ->color('warning'),
            ];
        }

        $totalAllocated = $budgetPockets->sum('allocated_amount');
        $totalSpent = $budgetPockets->sum(function ($bp) {
            return $bp->transactions()->where('type', 'expense')->sum('amount');
        });
        $totalIncome = $budgetPockets->sum(function ($bp) {
            return $bp->transactions()->where('type', 'income')->sum('amount');
        });
        $totalBalance = $budgetPockets->sum(fn($bp) => $bp->balance());
        $utilizationPercentage = $totalAllocated > 0 ? ($totalSpent / $totalAllocated) * 100 : 0;

        $stats = [];

        // Add summary stat first
        $stats[] = Stat::make('Budget Overview', 'IDR ' . number_format($totalAllocated, 0, ',', '.'))
            ->description($latestBudget->name . ' - ' . $budgetPockets->count() . ' pockets, ' . number_format($utilizationPercentage, 1) . '% utilized')
            ->descriptionIcon('heroicon-o-wallet')
            ->color('info');

        // Add individual pocket stats
        foreach ($budgetPockets as $budgetPocket) {
            $pocketSpent = $budgetPocket->transactions()->where('type', 'expense')->sum('amount');
            $pocketIncome = $budgetPocket->transactions()->where('type', 'income')->sum('amount');
            $pocketBalance = $budgetPocket->balance();
            $pocketUtilization = $budgetPocket->allocated_amount > 0 
                ? ($pocketSpent / $budgetPocket->allocated_amount) * 100 
                : 0;
            $transactionCount = $budgetPocket->transactions()->count();

            $stats[] = Stat::make($budgetPocket->pocket->name, 'IDR ' . number_format($pocketBalance, 0, ',', '.'))
                ->description('Allocated: IDR ' . number_format($budgetPocket->allocated_amount, 0, ',', '.') . ' | ' . $transactionCount . ' transactions')
                ->descriptionIcon($pocketBalance < 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                ->color($pocketBalance < 0 ? 'danger' : ($pocketUtilization > 80 ? 'warning' : 'success'));
        }

        return $stats;
    }
}
