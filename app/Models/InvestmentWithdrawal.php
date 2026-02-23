<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentWithdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'investment_id',
        'amount',
        'explanation',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }
}
