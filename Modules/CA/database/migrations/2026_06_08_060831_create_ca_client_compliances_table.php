<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_client_compliances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ca_client_id')->constrained('ca_clients')->cascadeOnDelete();
            $table->foreignId('ca_compliance_id')->constrained('ca_compliances')->cascadeOnDelete();
            
            $table->string('status')->default('active')->index();
            $table->timestamp('assigned_at')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            $table->unique(['ca_client_id', 'ca_compliance_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_client_compliances');
    }
};
