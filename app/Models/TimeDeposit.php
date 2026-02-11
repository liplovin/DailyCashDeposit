<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeDeposit extends Model
{
    protected $fillable = ['time_deposit_name', 'account_number', 'beginning_balance'];
}
