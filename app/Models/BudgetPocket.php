<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetPocket extends Model
{
    protected $guarded = ['id'];
    public function balance()
{
    $income = $this->transactions()->where('type', 'income')->sum('amount');
    $expense = $this->transactions()->where('type', 'expense')->sum('amount');
    return $this->allocated_amount + $income - $expense;
}

    /**
     * Get the budget that owns the budget pocket.
     */
    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Get the pocket associated with the budget pocket.
     */
    public function pocket()
    {
        return $this->belongsTo(Pocket::class);
    }
    /**
     * Get the transactions for the budget pocket.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
