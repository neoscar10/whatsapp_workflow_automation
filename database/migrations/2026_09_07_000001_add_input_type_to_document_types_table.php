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
        Schema::table('document_types', function (Blueprint $table) {
            $table->string('input_type')->default('document')->after('description'); // 'document' or 'input'
        });

        Schema::table('company_verification_document_versions', function (Blueprint $table) {
            $table->text('text_value')->nullable()->after('file_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->dropColumn('input_type');
        });

        Schema::table('company_verification_document_versions', function (Blueprint $table) {
            $table->dropColumn('text_value');
        });
    }
};
