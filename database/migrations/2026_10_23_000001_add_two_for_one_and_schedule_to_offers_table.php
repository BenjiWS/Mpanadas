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
        Schema::table('offers', function (Blueprint $table) {
            // two_for_one: 2 × 1, no usa discount_value.
            $table->enum('discount_type', ['percentage', 'fixed', 'two_for_one'])->change(); // App\Enums\DiscountType

            // Horario dentro de la vigencia: días ISO separados por coma (1 = lunes ... 7 = domingo)
            // y franja horaria. Null = todos los días / todo el día.
            $table->string('weekdays', 13)->nullable()->after('ends_at');
            $table->time('start_time')->nullable()->after('weekdays');
            $table->time('end_time')->nullable()->after('start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['weekdays', 'start_time', 'end_time']);
            $table->enum('discount_type', ['percentage', 'fixed'])->change();
        });
    }
};
