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
    ];
}
