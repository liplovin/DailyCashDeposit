<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashInfusion extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_infusion_name',
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
}
