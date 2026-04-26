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
        Schema::table(Tables::MEDICAL_RECORDS->value, function (Blueprint $table) {
            $table->foreignId('triage_log_id')->constrained(Tables::TRIAGE_LOGS->value)->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(Tables::MEDICAL_RECORDS->value, function (Blueprint $table) {
            $table->dropColumn('triage_log_id');
        });
    }
};
