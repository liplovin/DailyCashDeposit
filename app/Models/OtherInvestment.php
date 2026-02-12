<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherInvestment extends Model
{
    use HasFactory;

    protected $fillable = [
        'other_investment_name',
        'account_number',
        'beginning_balance',
        'maturity_date',
    ];
}
