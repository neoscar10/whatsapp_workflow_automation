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
        Schema::table('ca_compliance_requirements', function (Blueprint $table) {
            $table->string('required_when')->nullable()->default('Required Now')->after('is_recurring');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ca_compliance_requirements', function (Blueprint $table) {
            $table->dropColumn('required_when');
        });
    }
};
