<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtherInvestmentRenewal extends Model
{
    protected $fillable = ['other_investment_id', 'previous_maturity_date', 'new_maturity_date', 'explanation'];

    public function otherInvestment()
    {
        return $this->belongsTo(OtherInvestment::class);
    }
}
