<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_ai_cache', function (Blueprint $table) {
            $table->id();
            $table->string('cache_key')->unique();
            $table->string('request_hash')->index();
            $table->text('prompt');
            $table->json('response_json');
            $table->string('model_used')->nullable();
            $table->integer('token_usage')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_ai_cache');
    }
};
