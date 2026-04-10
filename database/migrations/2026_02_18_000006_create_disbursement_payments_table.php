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
        // Check if operating_account_disbursements table exists
        if (!Schema::hasTable('operating_account_disbursements')) {
            return;
        }

        // Remove old payment_for and payable_to columns if they exist
        if (Schema::hasTable('operating_account_disbursements')) {
            Schema::table('operating_account_disbursements', function (Blueprint $table) {
                if (Schema::hasColumn('operating_account_disbursements', 'payment_for')) {
                    $table->dropColumn('payment_for');
                }
                if (Schema::hasColumn('operating_account_disbursements', 'payable_to')) {
                    $table->dropColumn('payable_to');
                }
            });
        }

        // Create disbursement_payments table if it doesn't exist
        if (!Schema::hasTable('disbursement_payments')) {
            Schema::create('disbursement_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('operating_account_disbursement_id');
                $table->string('payment_for');
                $table->string('payable_to');
                $table->decimal('amount', 15, 2)->default(0);
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disbursement_payments');

        // Restore payment_for and payable_to columns
        if (Schema::hasTable('operating_account_disbursements')) {
            Schema::table('operating_account_disbursements', function (Blueprint $table) {
                $table->string('payment_for')->nullable();
                $table->string('payable_to')->nullable();
            });
        }
    }
};
