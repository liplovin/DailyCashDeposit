<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DollarRenewal extends Model
{
    use HasFactory;

    protected $fillable = [
        'dollar_id',
        'previous_maturity_date',
        'new_maturity_date',
        'explanation',
    ];

    public function dollar()
    {
        return $this->belongsTo(Dollar::class);
    }
}
