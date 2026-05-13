<?php

use App\Enums\Tables;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Tables::USERS->value, function (Blueprint $table): void {
            $table->string('messenger_user_id')->unique()->nullable()->default(null)->after('viber_user_id');
        });
    }

    public function down(): void
    {
        Schema::table(Tables::USERS->value, function (Blueprint $table): void {
            $table->dropColumn('messenger_user_id');
        });
    }
};
