<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pocket extends Model
{
    protected $guarded = ['id'];

    /**
     * Get the budget that owns the pocket.
     */
    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Get the transactions for the pocket.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
