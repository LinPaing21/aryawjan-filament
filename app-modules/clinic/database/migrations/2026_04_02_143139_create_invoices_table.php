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
        Schema::create(Tables::INVOICES->value, function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained(Tables::MEDICAL_RECORDS->value)->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained(Tables::USERS->value)->cascadeOnDelete();
            $table->decimal('consultation_fee', 10, 2)->default(0);
            $table->decimal('medicine_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('status')->default('unpaid'); // unpaid, paid
            $table->string('payment_method')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Tables::INVOICES->value);
    }
};
