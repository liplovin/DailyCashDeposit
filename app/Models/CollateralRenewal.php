<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollateralRenewal extends Model
{
    protected $fillable = [
        'collateral_id',
        'previous_maturity_date',
        'new_maturity_date',
        'explanation',
    ];

    public function collateral()
    {
        return $this->belongsTo(Collateral::class);
    }
}
