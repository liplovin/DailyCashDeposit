<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollateralWithdrawal extends Model
{
    protected $fillable = [
        'collateral_id',
        'amount',
        'explanation',
    ];

    public function collateral()
    {
        return $this->belongsTo(Collateral::class);
    }
}
