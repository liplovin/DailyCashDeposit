<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GovernmentSecurity extends Model
{
    protected $fillable = ['government_security_name', 'reference_number', 'beginning_balance', 'maturity_date'];
}
