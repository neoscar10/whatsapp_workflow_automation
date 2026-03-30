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
        Schema::create('automation_trigger_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('key')->index(); // unique within scope
            $table->string('name');
            $table->string('category'); // time, event, behavior, webhook, conditional
            $table->string('subtype')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_read_only')->default(false);
            $table->json('config_schema')->nullable();
            $table->json('default_config')->nullable();
            $table->json('default_output_variables')->nullable();
            $table->boolean('status')->default(true);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_trigger_definitions');
    }
};
