<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatingAccountRenewal extends Model
{
    use HasFactory;

    protected $fillable = [
        'operating_account_id',
        'previous_maturity_date',
        'new_maturity_date',
        'explanation',
    ];

    protected $casts = [
        'previous_maturity_date' => 'date',
        'new_maturity_date' => 'date',
    ];

    /**
     * Get the operating account that owns this renewal.
     */
    public function operatingAccount()
    {
        return $this->belongsTo(OperatingAccount::class);
    }
}
