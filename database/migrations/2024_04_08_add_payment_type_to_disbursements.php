<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('operating_account_disbursements', function (Blueprint $table) {
            // Add payment_type column if it doesn't exist
            if (!Schema::hasColumn('operating_account_disbursements', 'payment_type')) {
                $table->string('payment_type')->default('CHECK')->after('check_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operating_account_disbursements', function (Blueprint $table) {
            if (Schema::hasColumn('operating_account_disbursements', 'payment_type')) {
                $table->dropColumn('payment_type');
            }
        });
    }
};
