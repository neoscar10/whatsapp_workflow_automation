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
        Schema::create('whatsapp_template_buttons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_template_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // quick_reply, url, phone_number
            $table->string('text');
            $table->string('url')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('example_value')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_template_buttons');
    }
};
