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
        // Precio del extra en este producto; si es null se cobra ingredients.extra_price.
        Schema::table('product_extras', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('ingredient_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_extras', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
