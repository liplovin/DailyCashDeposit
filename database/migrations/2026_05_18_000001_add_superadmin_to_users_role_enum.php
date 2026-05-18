<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','treasury','treasury2','treasury3','accounting','accounting2','superadmin') NOT NULL DEFAULT 'accounting'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','treasury','treasury2','treasury3','accounting','accounting2') NOT NULL DEFAULT 'accounting'");
    }
};
