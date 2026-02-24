<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GovernmentSecurity extends Model
{
    protected $fillable = ['government_security_name', 'reference_number', 'beginning_balance', 'collection', 'collection_date', 'disbursement', 'disbursement_date', 'ending_balance', 'maturity_date', 'acquisition_date', 'explanation'];

    public function renewals()
    {
        return $this->hasMany(GovernmentSecurityRenewal::class)->orderBy('created_at', 'desc');
    }

    public function withdrawals()
    {
        return $this->hasMany(GovernmentSecurityWithdrawal::class)->orderBy('created_at', 'desc');
    }

    public function balances()
    {
        return $this->hasMany(GovernmentSecurityBalance::class)->orderBy('created_at', 'desc');
    }
}

