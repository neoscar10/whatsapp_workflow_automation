<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_document_types', function (Blueprint $table) {
            $table->id();
            
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            
            $table->json('allowed_extensions')->nullable(); // e.g. ["jpg", "png", "pdf"]
            $table->json('allowed_mime_types')->nullable(); // e.g. ["image/jpeg", "application/pdf"]
            $table->integer('max_file_size')->default(5120); // in KB (5MB)
            
            // preview_type: image, pdf, document, spreadsheet, archive, text
            $table->string('preview_type')->default('document');
            
            $table->string('status')->default('active')->index();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_document_types');
    }
};
