<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update NULL values to empty strings
        DB::table('collections')->whereNull('assured')->update(['assured' => '']);
        DB::table('collections')->whereNull('policy_number')->update(['policy_number' => '']);
        DB::table('collections')->whereNull('broker_agent')->update(['broker_agent' => '']);
        
        // Now change columns to NOT NULL
        Schema::table('collections', function (Blueprint $table) {
            $table->string('assured')->nullable(false)->change();
            $table->string('policy_number')->nullable(false)->change();
            $table->string('broker_agent')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->string('assured')->nullable()->change();
            $table->string('policy_number')->nullable()->change();
            $table->string('broker_agent')->nullable()->change();
        });
    }
};
