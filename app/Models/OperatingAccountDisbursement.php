<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatingAccountDisbursement extends Model
{
    protected $fillable = [
        'operating_account_id',
        'check_number',
        'payment_type',
        'date',
        'amount',
        'status',
    ];

    /**
     * Get the operating account associated with the disbursement.
     */
    public function operatingAccount()
    {
        return $this->belongsTo(OperatingAccount::class);
    }

    /**
     * Get all payments for this disbursement.
     */
    public function payments()
    {
        return $this->hasMany(DisbursementPayment::class, 'operating_account_disbursement_id');
    }
}
