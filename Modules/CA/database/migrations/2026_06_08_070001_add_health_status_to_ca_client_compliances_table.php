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
        Schema::table('ca_client_compliances', function (Blueprint $table) {
            $table->string('health_status')->default('pending')->after('status')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ca_client_compliances', function (Blueprint $table) {
            $table->dropColumn('health_status');
        });
    }
};
