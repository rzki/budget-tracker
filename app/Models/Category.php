<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Category extends Model
{
    protected $guarded = ['id'];

    public function budgets()
    {
        return $this->belongsToMany(Budget::class, 'budget_categories')
                    ->withPivot('allocated_amount')
                    ->withTimestamps();
    }
}
