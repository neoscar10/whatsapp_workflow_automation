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
        Schema::create('whatsapp_simulated_media', function (Blueprint $table) {
            $table->id();
            $table->string('simulated_media_id')->unique();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('uploaded_by');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->string('extension');
            $table->unsignedBigInteger('file_size');
            $table->string('storage_disk');
            $table->string('storage_path');
            $table->timestamps();

            // Foreign keys / indexes
            $table->index(['company_id', 'contact_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_simulated_media');
    }
};
