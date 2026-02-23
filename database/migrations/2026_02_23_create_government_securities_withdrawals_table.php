<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('government_securities_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('government_security_id');
            $table->decimal('amount', 15, 2);
            $table->text('explanation')->nullable();
            $table->timestamps();
            
            $table->foreign('government_security_id')->references('id')->on('government_securities')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('government_securities_withdrawals');
    }
};
