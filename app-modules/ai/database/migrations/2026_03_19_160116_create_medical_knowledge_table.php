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
        Schema::create(Tables::MEDICAL_KNOWLEDGES->value, function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->vector('embedding', 3072);
            $table->json('metadata');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Tables::MEDICAL_KNOWLEDGES->value);
    }
};
