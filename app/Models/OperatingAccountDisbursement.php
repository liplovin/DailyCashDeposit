<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatingAccountDisbursement extends Model
{
    protected $fillable = [
        'operating_account_id',
        'check_number',
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
}
