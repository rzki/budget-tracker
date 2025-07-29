<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $guarded = ['id'];

    public function budgetPockets()
    {
        return $this->hasMany(BudgetPocket::class);
    }
}
