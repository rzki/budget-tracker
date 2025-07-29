<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Budget;
use App\Models\BudgetPocket;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

final class PocketCardsWidget extends Widget
{
    protected static string $view = 'filament.widgets.pocket-cards';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 4;

    /**
     * Get pocket cards from the latest budget
     */
    public function getPocketCards(): Collection
    {
        $latestBudget = Budget::latest()->first();
        
        if (!$latestBudget) {
            return collect([]);
        }

        return BudgetPocket::with(['pocket', 'budget'])
            ->where('budget_id', $latestBudget->id)
            ->get()
            ->map(function (BudgetPocket $budgetPocket) {
                $spent = $budgetPocket->transactions()
                    ->where('type', 'expense')
                    ->sum('amount');
                
                $spentPercentage = $budgetPocket->allocated_amount > 0 
                    ? min(100, ($spent / $budgetPocket->allocated_amount) * 100)
                    : 0;

                return [
                    'id' => $budgetPocket->id,
                    'name' => $budgetPocket->pocket->name,
                    'budget_name' => $budgetPocket->budget->name,
                    'allocated_amount' => $budgetPocket->allocated_amount,
                    'spent' => $spent,
                    'balance' => $budgetPocket->balance(),
                    'spent_percentage' => $spentPercentage,
                    'status' => $this->getBalanceStatus($budgetPocket),
                ];
            });
    }

    /**
     * Get balance status for styling
     */
    private function getBalanceStatus(BudgetPocket $budgetPocket): string
    {
        $balance = $budgetPocket->balance();
        $allocated = $budgetPocket->allocated_amount;

        if ($balance < 0) {
            return 'danger'; // Overspent
        } elseif ($balance < $allocated * 0.2) {
            return 'warning'; // Low balance (less than 20%)
        } else {
            return 'success'; // Good balance
        }
    }

    /**
     * Get the latest budget name
     */
    public function getLatestBudgetName(): string
    {
        $latestBudget = Budget::latest()->first();
        return $latestBudget ? $latestBudget->name : 'No Budget Available';
    }
}
