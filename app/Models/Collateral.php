<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collateral extends Model
{
    protected $fillable = [
        'collateral',
        'account_number',
        'beginning_balance',
        'collection',
        'collection_date',
        'disbursement',
        'disbursement_date',
        'ending_balance',
        'maturity_date',
    ];
}
