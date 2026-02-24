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
        Schema::create('operating_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('operating_account_name')->nullable();
            $table->string('account_number')->unique();
            $table->decimal('beginning_balance', 15, 2);
            $table->decimal('collection', 15, 2)->default(0);
            $table->decimal('disbursement', 15, 2)->default(0);
            $table->decimal('ending_balance', 15, 2)->default(0);
            $table->date('maturity_date')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->text('explanation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operating_accounts');
    }
};
