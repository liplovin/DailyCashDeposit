<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corporate_bonds', function (Blueprint $table) {
            $table->id();
            $table->string('corporate_bond_name')->nullable();
            $table->string('account_number')->unique();
            $table->decimal('beginning_balance', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corporate_bonds');
    }
};
