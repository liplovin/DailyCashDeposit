<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorporateBond extends Model
{
    use HasFactory;

    protected $fillable = [
        'corporate_bond_name',
        'account_number',
        'beginning_balance',
    ];
}
