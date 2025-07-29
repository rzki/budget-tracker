<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Transaction extends Model
{
    protected $guarded = ['id'];
    
    public function pocket()
    {
        return $this->belongsTo(Pocket::class);
    }
}
