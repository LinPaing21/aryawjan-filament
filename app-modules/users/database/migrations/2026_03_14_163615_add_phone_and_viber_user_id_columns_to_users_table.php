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
        Schema::table(Tables::USERS->value, function (Blueprint $table) {
            $table->string('email')->nullable()->default(null)->change();

            $table->string('phone')->unique()->default(null)->nullable();
            $table->string('viber_user_id')->unique()->default(null)->nullable();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(Tables::USERS->value, function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();

            $table->dropColumn('phone');
            $table->dropColumn('viber_user_id');

            $table->dropSoftDeletes();
        });
    }
};
