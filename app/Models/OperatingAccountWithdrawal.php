<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatingAccountWithdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'operating_account_id',
        'amount',
        'explanation',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the operating account that owns this withdrawal.
     */
    public function operatingAccount()
    {
        return $this->belongsTo(OperatingAccount::class);
    }
}
