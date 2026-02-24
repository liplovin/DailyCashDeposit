<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collateral extends Model
{
    protected $fillable = [
        'collateral',
        'account_number',
        'beginning_balance',
        'collection',
        'collection_date',
        'disbursement',
        'disbursement_date',
        'ending_balance',
        'maturity_date',
        'acquisition_date',
        'explanation',
    ];

    /**
     * Get all renewals for this collateral
     */
    public function renewals()
    {
        return $this->hasMany(CollateralRenewal::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all withdrawals for this collateral
     */
    public function withdrawals()
    {
        return $this->hasMany(CollateralWithdrawal::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all balance additions for this collateral
     */
    public function balances()
    {
        return $this->hasMany(CollateralBalance::class)->orderBy('created_at', 'desc');
    }
}
