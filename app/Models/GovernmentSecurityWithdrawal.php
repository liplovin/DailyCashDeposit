<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GovernmentSecurityWithdrawal extends Model
{
    protected $fillable = ['government_security_id', 'amount', 'explanation'];
    protected $table = 'government_securities_withdrawals';

    public function governmentSecurity()
    {
        return $this->belongsTo(GovernmentSecurity::class);
    }
}
