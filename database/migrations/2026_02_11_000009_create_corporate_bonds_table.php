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
            $table->decimal('collection', 15, 2)->default(0);
            $table->decimal('disbursement', 15, 2)->default(0);
            $table->decimal('ending_balance', 15, 2)->default(0);
            $table->date('maturity_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corporate_bonds');
    }
};
