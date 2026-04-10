<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * This migration has been moved to 2024_04_10_000000_create_disbursement_payments_table.php
     * The amount column is now included in the disbursement_payments table creation.
     */
    public function up(): void
    {
        // This migration is deprecated - amount column is already in the main creation migration
    }

    public function down(): void
    {
        // This migration is deprecated
    }
};

