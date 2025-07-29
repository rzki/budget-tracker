<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Category extends Model
{
    use HasUuids;
    protected $guarded = ['id'];

    public function budgets()
    {
        return $this->belongsToMany(Budget::class, 'budget_categories')
                    ->withPivot('allocated_amount')
                    ->withTimestamps();
    }
}
