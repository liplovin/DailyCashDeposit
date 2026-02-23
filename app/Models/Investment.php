<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    use HasFactory;

    protected $fillable = [
        'investment_name',
        'reference_number',
        'beginning_balance',
        'maturity_date',
        'collection',
        'collection_date',
        'disbursement',
        'disbursement_date',
        'ending_balance',
    ];

    protected $casts = [
        'beginning_balance' => 'decimal:2',
        'collection' => 'decimal:2',
        'disbursement' => 'decimal:2',
        'ending_balance' => 'decimal:2',
    ];

    /**
     * Get all renewals for this investment
     */
    public function renewals()
    {
        return $this->hasMany(InvestmentRenewal::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all withdrawals for this investment
     */
    public function withdrawals()
    {
        return $this->hasMany(InvestmentWithdrawal::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all balance additions for this investment
     */
    public function balances()
    {
        return $this->hasMany(InvestmentBalance::class)->orderBy('created_at', 'desc');
    }
}
