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
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operating_account_id');
            $table->decimal('collection_amount', 15, 2);
            $table->string('deposit_slip')->nullable();
            $table->string('check')->nullable();
            $table->enum('status', ['pending', 'processed'])->default('pending');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('operating_account_id')
                ->references('id')
                ->on('operating_accounts')
                ->onDelete('cascade');

            // Index for faster queries
            $table->index('operating_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
