<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_compliance_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ca_compliance_id')->constrained('ca_compliances')->cascadeOnDelete();
            
            $table->string('name');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            
            // requirement_type: document, text, date, boolean, etc.
            $table->string('requirement_type')->default('document');
            
            // input_type: file, image, pdf, text, textarea, date, select, checkbox
            $table->string('input_type')->default('file');
            
            $table->boolean('is_required')->default(true);
            $table->boolean('is_recurring')->default(false);
            
            $table->string('status')->default('active')->index();
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_compliance_requirements');
    }
};
