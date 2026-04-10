<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * This migration has been moved to 2026_02_18_000005_create_operating_account_disbursements_table.php
     * The payment_type column is now included in the main table creation.
     */
    public function up(): void
    {
        // This migration is deprecated - payment_type column is already in the main creation migration
    }

    public function down(): void
    {
        // This migration is deprecated
    }
};
