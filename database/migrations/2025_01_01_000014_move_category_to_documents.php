<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove category_id from users
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });

        // Add category_id to documents
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('user_id')
                  ->constrained('categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('role')
                  ->constrained('categories')->nullOnDelete();
        });
    }
};
