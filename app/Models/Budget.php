<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasUuids;
    protected $guarded = ['id'];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'budget_categories')
                    ->withPivot('allocated_amount')
                    ->withTimestamps();
    }
}
