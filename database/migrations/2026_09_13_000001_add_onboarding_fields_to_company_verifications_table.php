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
            $table->string('business_type')->nullable()->after('company_id');
            $table->string('legal_name')->nullable()->after('business_type');
            $table->string('display_name')->nullable()->after('legal_name');
            $table->string('category')->nullable()->after('display_name');
            $table->string('website')->nullable()->after('category');
            $table->text('address')->nullable()->after('website');
            $table->string('signatory_name')->nullable()->after('address');
            $table->string('signatory_designation')->nullable()->after('signatory_name');
            $table->string('business_email')->nullable()->after('signatory_designation');
            $table->string('business_phone')->nullable()->after('business_email');
            $table->string('wa_phone')->nullable()->after('business_phone');
            $table->timestamp('submitted_at')->nullable()->after('last_activity_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_verifications', function (Blueprint $table) {
            $table->dropColumn([
                'business_type',
                'legal_name',
                'display_name',
                'category',
                'website',
                'address',
                'signatory_name',
                'signatory_designation',
                'business_email',
                'business_phone',
                'wa_phone',
                'submitted_at',
            ]);
        });
    }
};
