<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatingAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'operating_account_name',
        'account_number',
        'beginning_balance',
    ];
}
