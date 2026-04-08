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
        // Add new columns to operating_account_disbursements table
        Schema::table('operating_account_disbursements', function (Blueprint $table) {
            // Remove old payment_for and payable_to if they exist
            if (Schema::hasColumn('operating_account_disbursements', 'payment_for')) {
                $table->dropColumn('payment_for');
            }
            if (Schema::hasColumn('operating_account_disbursements', 'payable_to')) {
                $table->dropColumn('payable_to');
            }
        });

        // Create disbursement_payments table
        Schema::create('disbursement_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operating_account_disbursement_id');
            $table->string('payment_for');
            $table->string('payable_to');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('operating_account_disbursement_id')
                ->references('id')
                ->on('operating_account_disbursements')
                ->onDelete('cascade');

            // Index for faster queries
            $table->index('operating_account_disbursement_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disbursement_payments');

        // Restore payment_for and payable_to columns
        Schema::table('operating_account_disbursements', function (Blueprint $table) {
            $table->string('payment_for')->nullable();
            $table->string('payable_to')->nullable();
        });
    }
};
