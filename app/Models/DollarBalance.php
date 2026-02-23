<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DollarBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'dollar_id',
        'amount',
        'explanation',
    ];

    public function dollar()
    {
        return $this->belongsTo(Dollar::class);
    }
}
