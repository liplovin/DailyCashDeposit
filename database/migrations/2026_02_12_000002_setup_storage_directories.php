<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Run the migrations - ensure storage directories exist.
     */
    public function up(): void
    {
        // Create necessary directories
        $directories = [
            storage_path('app/public/deposit-slips'),
        ];

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
        }

        // Link storage if needed
        if (!file_exists(public_path('storage'))) {
            Artisan::call('storage:link');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep directories for safety
    }
};
