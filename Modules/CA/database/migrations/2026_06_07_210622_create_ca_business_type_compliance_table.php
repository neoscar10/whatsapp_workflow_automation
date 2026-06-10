<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_business_type_compliance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ca_business_type_id')->constrained('ca_business_types')->cascadeOnDelete();
            $table->foreignId('ca_compliance_id')->constrained('ca_compliances')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ca_business_type_id', 'ca_compliance_id'], 'ca_biz_type_comp_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_business_type_compliance');
    }
};
