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
        Schema::create('time_deposits', function (Blueprint $table) {
            $table->id();
            $table->string('time_deposit_name')->nullable();
            $table->string('account_number')->unique();
            $table->decimal('beginning_balance', 15, 2)->default(0);
            $table->decimal('collection', 15, 2)->default(0);
            $table->date('collection_date')->nullable();
            $table->decimal('disbursement', 15, 2)->default(0);
            $table->date('disbursement_date')->nullable();
            $table->decimal('ending_balance', 15, 2)->default(0);
            $table->date('maturity_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_deposits');
    }
};
