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
        Schema::create('document_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->enum('role', ['SIGNER', 'VIEWER'])->default('SIGNER');
            $table->enum('status', ['PENDING', 'SIGNED', 'VIEWED', 'REJECTED'])->default('PENDING');
            $table->integer('signing_order')->default(1);
            $table->string('signature_token')->unique()->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->text('signature_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_recipients');
    }
};
