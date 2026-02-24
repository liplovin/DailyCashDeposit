<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherInvestment extends Model
{
    use HasFactory;

    protected $fillable = [
        'other_investment_name',
        'account_number',
        'beginning_balance',
        'maturity_date',
        'acquisition_date',
        'explanation',
        'collection',
        'collection_date',
        'disbursement',
        'disbursement_date',
        'ending_balance',
    ];

    public function renewals()
    {
        return $this->hasMany(OtherInvestmentRenewal::class)->orderBy('created_at', 'desc');
    }

    public function withdrawals()
    {
        return $this->hasMany(OtherInvestmentWithdrawal::class)->orderBy('created_at', 'desc');
    }

    public function balances()
    {
        return $this->hasMany(OtherInvestmentBalance::class)->orderBy('created_at', 'desc');
    }
}
