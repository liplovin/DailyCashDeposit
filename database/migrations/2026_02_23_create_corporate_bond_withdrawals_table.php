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
        Schema::create('corporate_bond_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corporate_bond_id')->constrained('corporate_bonds')->onDelete('cascade');
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
        Schema::dropIfExists('corporate_bond_withdrawals');
    }
};
