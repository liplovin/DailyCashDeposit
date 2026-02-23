<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtherInvestmentRenewal extends Model
{
    protected $fillable = ['other_investment_id', 'new_maturity_date'];

    public function otherInvestment()
    {
        return $this->belongsTo(OtherInvestment::class);
    }
}
