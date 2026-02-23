<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashInfusionWithdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_infusion_id',
        'amount',
        'explanation',
    ];

    public function cashInfusion()
    {
        return $this->belongsTo(CashInfusion::class);
    }
}
