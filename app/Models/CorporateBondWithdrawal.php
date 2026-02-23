<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorporateBondWithdrawal extends Model
{
    protected $fillable = [
        'corporate_bond_id',
        'amount',
        'explanation',
    ];

    public function corporateBond()
    {
        return $this->belongsTo(CorporateBond::class);
    }
}
