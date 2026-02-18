<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disbursement extends Model
{
    protected $fillable = [
        'collateral_id',
        'check_number',
        'date',
        'amount',
        'status',
    ];

    /**
     * Get the collateral associated with the disbursement.
     */
    public function collateral()
    {
        return $this->belongsTo(Collateral::class);
    }
}
