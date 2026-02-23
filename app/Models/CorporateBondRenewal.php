<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorporateBondRenewal extends Model
{
    protected $fillable = [
        'corporate_bond_id',
        'previous_maturity_date',
        'new_maturity_date',
        'explanation',
    ];

    public function corporateBond()
    {
        return $this->belongsTo(CorporateBond::class);
    }
}
