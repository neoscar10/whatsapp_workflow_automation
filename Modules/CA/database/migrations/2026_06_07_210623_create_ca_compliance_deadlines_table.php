<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_compliance_deadlines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ca_compliance_id')->constrained('ca_compliances')->cascadeOnDelete();
            $table->string('frequency'); // monthly, quarterly, half_yearly, annually, one_time
            $table->integer('due_day')->nullable(); // e.g. 15 for 15th of the month
            $table->integer('due_month')->nullable(); // e.g. 3 for March
            $table->integer('reminder_window')->default(7); // Days before deadline to start reminders
            $table->text('description')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_compliance_deadlines');
    }
};
