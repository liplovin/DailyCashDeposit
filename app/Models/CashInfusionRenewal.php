<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashInfusionRenewal extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_infusion_id',
        'previous_maturity_date',
        'new_maturity_date',
        'explanation',
    ];

    public function cashInfusion()
    {
        return $this->belongsTo(CashInfusion::class);
    }
}
