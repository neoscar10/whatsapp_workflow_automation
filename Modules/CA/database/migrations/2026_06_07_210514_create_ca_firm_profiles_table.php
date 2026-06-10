<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_firm_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('icai_registration_number')->nullable()->index();
            $table->string('firm_type')->nullable();
            $table->string('firm_email')->nullable();
            $table->string('firm_phone')->nullable();
            $table->string('firm_address')->nullable();
            $table->string('firm_city')->nullable();
            $table->string('firm_state')->nullable();
            $table->string('firm_country')->default('IN');
            $table->json('settings_json')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_firm_profiles');
    }
};
