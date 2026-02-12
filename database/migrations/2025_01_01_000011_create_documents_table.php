<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('signed_file_path')->nullable();
            $table->string('document_hash')->unique();
            $table->enum('status', ['draft', 'signed', 'revoked'])->default('draft');
            $table->json('qr_position')->nullable(); // {x, y, page, width, height}
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
