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
        Schema::table('disbursement_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('disbursement_payments', 'amount')) {
                $table->decimal('amount', 15, 2)->default(0)->after('payable_to');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disbursement_payments', function (Blueprint $table) {
            if (Schema::hasColumn('disbursement_payments', 'amount')) {
                $table->dropColumn('amount');
            }
        });
    }
};
