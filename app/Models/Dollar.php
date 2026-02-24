<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dollar extends Model
{
    use HasFactory;

    protected $fillable = [
        'dollar_name',
        'account_number',
        'beginning_balance',
        'maturity_date',
        'collection',
        'collection_date',
        'disbursement',
        'disbursement_date',
        'ending_balance',
        'acquisition_date',
        'explanation',
    ];

    protected $casts = [
        'beginning_balance' => 'decimal:2',
        'collection' => 'decimal:2',
        'disbursement' => 'decimal:2',
        'ending_balance' => 'decimal:2',
    ];

    public function renewals()
    {
        return $this->hasMany(DollarRenewal::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(DollarWithdrawal::class);
    }

    public function balances()
    {
        return $this->hasMany(DollarBalance::class);
    }
}
