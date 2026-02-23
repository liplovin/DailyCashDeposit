<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GovernmentSecurityBalance extends Model
{
    protected $fillable = ['government_security_id', 'amount', 'reason'];
    protected $table = 'government_securities_balances';

    public function governmentSecurity()
    {
        return $this->belongsTo(GovernmentSecurity::class);
    }
}
