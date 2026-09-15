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
        Schema::table('company_verifications', function (Blueprint $table) {
            if (!Schema::hasColumn('company_verifications', 'rejection_reason')) {
                $table->string('rejection_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('company_verifications', 'rejection_notes')) {
                $table->text('rejection_notes')->nullable()->after('rejection_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_verifications', function (Blueprint $table) {
            if (Schema::hasColumn('company_verifications', 'rejection_notes')) {
                $table->dropColumn('rejection_notes');
            }
            if (Schema::hasColumn('company_verifications', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
};
