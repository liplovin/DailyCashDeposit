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
        Schema::create('dollar_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dollar_id')->constrained('dollars')->onDelete('cascade');
            $table->date('previous_maturity_date');
            $table->date('new_maturity_date');
            $table->text('explanation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dollar_renewals');
    }
};
