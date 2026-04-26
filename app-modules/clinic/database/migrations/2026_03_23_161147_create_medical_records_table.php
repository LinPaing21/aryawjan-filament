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
        Schema::create(Tables::MEDICAL_RECORDS->value, function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users');
            $table->text('symptoms');
            $table->text('physical_examination')->nullable(); // စမ်းသပ်ချက် သွေးပေါင်ချိန် ကိုယ်ပူချိန်
            $table->json('ai_suggestions')->nullable();
            $table->string('final_diagnosis'); // ရောဂါ ဘာကြောင့်ဖြစ်တာ
            $table->text('doctor_notes')->nullable(); // ဆောင်ရန် ရှောင်ရန် Notes

            $table->json('prescriptions')->nullable(); // ဆေးညွှန်း

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Tables::MEDICAL_RECORDS->value);
    }
};
