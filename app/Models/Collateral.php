<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collateral extends Model
{
    protected $fillable = [
        'collateral',
        'account_number',
        'beginning_balance',
        'maturity_date',
    ];
}
