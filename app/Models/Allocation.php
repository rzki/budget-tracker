<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Allocation extends Model
{
    protected $guarded = ['id'];

    /**
     * Get the budget that owns the allocation.
     */
    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Get the category that owns the allocation.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    /**
     * Get the transaction associated with the allocation.
     */
    public function transaction()
    {
        return $this->hasMany(Transaction::class);
    }
}
