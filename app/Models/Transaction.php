<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Transaction extends Model
{
    use HasUuids;
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
