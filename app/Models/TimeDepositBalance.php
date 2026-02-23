<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeDepositBalance extends Model
{
    protected $fillable = [
        'time_deposit_id',
        'amount',
        'explanation',
    ];

    public function timeDeposit()
    {
        return $this->belongsTo(TimeDeposit::class);
    }
}
