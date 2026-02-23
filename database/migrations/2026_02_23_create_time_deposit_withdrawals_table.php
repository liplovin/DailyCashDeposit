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
        Schema::create('time_deposit_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('time_deposit_id')->constrained('time_deposits')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->text('explanation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_deposit_withdrawals');
    }
};
