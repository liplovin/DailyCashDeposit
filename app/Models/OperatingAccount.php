<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatingAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'operating_account_name',
        'account_number',
        'beginning_balance',
        'maturity_date',
    ];

    /**
     * Get the collections for this operating account.
     */
    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    /**
     * Get the disbursements for the operating account.
     */
    public function disbursements()
    {
        return $this->hasMany(OperatingAccountDisbursement::class);
    }

    /**
     * Get the renewals for this operating account.
     */
    public function renewals()
    {
        return $this->hasMany(OperatingAccountRenewal::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the withdrawals for this operating account.
     */
    public function withdrawals()
    {
        return $this->hasMany(OperatingAccountWithdrawal::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the balances for this operating account.
     */
    public function balances()
    {
        return $this->hasMany(OperatingAccountBalance::class)->orderBy('created_at', 'desc');
    }
}
