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
            $table->string('payment_for')->nullable()->after('amount');
            $table->string('payable_to')->nullable()->after('payment_for');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operating_account_disbursements', function (Blueprint $table) {
            $table->dropColumn(['payment_for', 'payable_to']);
        });
    }
};
