<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_infusion_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_infusion_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->text('explanation');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_infusion_withdrawals');
    }
};
