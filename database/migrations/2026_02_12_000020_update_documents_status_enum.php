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
        // Update ENUM to include 'pending' status for documents with recipients
        DB::statement("ALTER TABLE `documents` MODIFY `status` ENUM('draft', 'pending', 'signed', 'revoked') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'pending' from ENUM (risky if data exists with 'pending')
        DB::statement("ALTER TABLE `documents` MODIFY `status` ENUM('draft', 'signed', 'revoked') NOT NULL DEFAULT 'draft'");
    }
};
