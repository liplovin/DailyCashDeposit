<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeDepositWithdrawal extends Model
{
    protected $fillable = [
        'time_deposit_id',
        'amount',
        'explanation',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function timeDeposit()
    {
        return $this->belongsTo(TimeDeposit::class);
    }
}
