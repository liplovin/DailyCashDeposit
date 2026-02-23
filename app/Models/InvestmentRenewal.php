<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentRenewal extends Model
{
    use HasFactory;

    protected $fillable = [
        'investment_id',
        'previous_maturity_date',
        'new_maturity_date',
        'explanation',
    ];

    protected $casts = [
        'previous_maturity_date' => 'date',
        'new_maturity_date' => 'date',
    ];

    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }
}
