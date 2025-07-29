<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Transaction extends Model
{
    protected $guarded = ['id'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
