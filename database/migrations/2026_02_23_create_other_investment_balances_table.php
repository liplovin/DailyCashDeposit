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
        Schema::create('other_investment_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('other_investment_id')->constrained('other_investments')->onDelete('cascade');
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
        Schema::dropIfExists('other_investment_balances');
    }
};
