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
        Schema::table(Tables::TRIAGE_LOGS->value, function (Blueprint $table) {
            $table->json('attachments')->nullable()->after('raw_symptoms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(Tables::TRIAGE_LOGS->value, function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
    }
};
