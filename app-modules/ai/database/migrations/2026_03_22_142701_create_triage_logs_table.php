<?php

use App\Enums\Tables;
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
        Schema::create(Tables::TRIAGE_LOGS->value, function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained(Tables::USERS->value)->cascadeOnDelete();
            $table->text('raw_symptoms')->nullable();
            $table->json('ai_analysis');
            $table->string('severity')->default('low');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Tables::TRIAGE_LOGS->value);
    }
};
