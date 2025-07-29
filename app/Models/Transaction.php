<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Transaction extends Model
{
    protected $guarded = ['id'];
    /**
     * Get the budget pocket that owns the transaction.
     */
    public function budgetPocket()
    {
        return $this->belongsTo(BudgetPocket::class);
    }
}
