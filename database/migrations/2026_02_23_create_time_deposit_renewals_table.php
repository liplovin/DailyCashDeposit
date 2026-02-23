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
        Schema::create('time_deposit_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('time_deposit_id')->constrained('time_deposits')->onDelete('cascade');
            $table->date('previous_maturity_date');
            $table->date('new_maturity_date');
            $table->text('explanation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_deposit_renewals');
    }
};
