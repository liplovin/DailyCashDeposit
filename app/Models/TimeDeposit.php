<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeDeposit extends Model
{
    protected $fillable = ['time_deposit_name', 'account_number', 'beginning_balance', 'collection', 'collection_date', 'disbursement', 'disbursement_date', 'ending_balance', 'maturity_date'];

    /**
     * Get all renewals for this time deposit
     */
    public function renewals()
    {
        return $this->hasMany(TimeDepositRenewal::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all withdrawals for this time deposit
     */
    public function withdrawals()
    {
        return $this->hasMany(TimeDepositWithdrawal::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all balance additions for this time deposit
     */
    public function balances()
    {
        return $this->hasMany(TimeDepositBalance::class)->orderBy('created_at', 'desc');
    }
}


