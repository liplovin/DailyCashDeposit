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
        Schema::create('operating_account_disbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operating_account_id')->constrained('operating_accounts')->onDelete('cascade');
            $table->string('check_number');
            $table->date('date');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'processed'])->default('pending');
            $table->timestamps();

            // Index for faster queries
            $table->index('operating_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operating_account_disbursements');
    }
};
