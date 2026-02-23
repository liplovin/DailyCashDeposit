<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GovernmentSecurityRenewal extends Model
{
    protected $fillable = ['government_security_id', 'previous_maturity_date', 'new_maturity_date', 'explanation'];
    protected $table = 'government_securities_renewals';

    public function governmentSecurity()
    {
        return $this->belongsTo(GovernmentSecurity::class);
    }
}
