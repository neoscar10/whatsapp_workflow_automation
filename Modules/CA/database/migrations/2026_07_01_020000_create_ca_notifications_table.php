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
        Schema::create('ca_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->foreignId('ca_client_id')->nullable()->constrained('ca_clients')->nullOnDelete();
            $table->unsignedBigInteger('contact_id')->nullable()->index();
            $table->string('type'); // document_received, document_matched, match_failed
            $table->string('title');
            $table->text('message');
            $table->string('status')->default('pending'); // pending, resolved, dismissed
            $table->json('metadata_json')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->string('action_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ca_notifications');
    }
};
