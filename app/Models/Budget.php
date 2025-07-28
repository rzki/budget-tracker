<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $guarded = ['id'];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'budget_categories')
                    ->withPivot('allocated_amount')
                    ->withTimestamps();
    }
}
