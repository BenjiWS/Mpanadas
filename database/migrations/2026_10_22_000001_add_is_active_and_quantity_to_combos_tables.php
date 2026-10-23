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
        // Permite sacar un combo de la carta sin borrarlo.
        Schema::table('combos', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('price');
        });

        // Cuántas unidades de cada producto trae el combo (ej. 3 empanadas de carne).
        Schema::table('combos_products', function (Blueprint $table) {
            $table->unsignedSmallInteger('quantity')->default(1)->after('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('combos_products', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });

        Schema::table('combos', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
