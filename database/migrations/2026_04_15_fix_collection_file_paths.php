<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix collection deposit_slip and check fields containing '0' values
        DB::table('collections')
            ->where(function ($query) {
                $query->where('deposit_slip', '0')
                    ->orWhere('deposit_slip', '')
                    ->orWhereNull('deposit_slip');
            })
            ->update(['deposit_slip' => null]);

        DB::table('collections')
            ->where(function ($query) {
                $query->where('check', '0')
                    ->orWhere('check', '')
                    ->orWhereNull('check');
            })
            ->update(['check' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be reversed safely
    }
};
