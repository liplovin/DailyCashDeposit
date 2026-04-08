<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisbursementPayment extends Model
{
    protected $table = 'disbursement_payments';
    
    protected $fillable = [
        'operating_account_disbursement_id',
        'payment_for',
        'payable_to',
        'amount',
    ];

    /**
     * Get the disbursement this payment belongs to.
     */
    public function disbursement()
    {
        return $this->belongsTo(OperatingAccountDisbursement::class, 'operating_account_disbursement_id');
    }
}
