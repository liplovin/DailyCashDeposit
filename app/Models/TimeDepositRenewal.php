<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeDepositRenewal extends Model
{
    use HasFactory;

    protected $table = 'time_deposit_renewals';

    protected $fillable = [
        'time_deposit_id',
        'previous_maturity_date',
        'new_maturity_date',
        'explanation'
    ];

    protected $casts = [
        'previous_maturity_date' => 'date',
        'new_maturity_date' => 'date',
    ];

    /**
     * Get the time deposit that owns this renewal
     */
    public function timeDeposit()
    {
        return $this->belongsTo(TimeDeposit::class);
    }
}
