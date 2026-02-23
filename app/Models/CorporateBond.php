<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorporateBond extends Model
{
    use HasFactory;

    protected $fillable = [
        'corporate_bond_name',
        'account_number',
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
     * Get all renewals for this corporate bond
     */
    public function renewals()
    {
        return $this->hasMany(CorporateBondRenewal::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all withdrawals for this corporate bond
     */
    public function withdrawals()
    {
        return $this->hasMany(CorporateBondWithdrawal::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all balance additions for this corporate bond
     */
    public function balances()
    {
        return $this->hasMany(CorporateBondBalance::class)->orderBy('created_at', 'desc');
    }
}
