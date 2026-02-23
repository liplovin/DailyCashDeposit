<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtherInvestmentWithdrawal extends Model
{
    protected $fillable = ['other_investment_id', 'amount', 'explanation'];

    public function otherInvestment()
    {
        return $this->belongsTo(OtherInvestment::class);
    }
}
