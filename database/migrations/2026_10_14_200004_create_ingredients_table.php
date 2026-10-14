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
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->text('description')->nullable();
            // Precio adicional cuando se agrega como extra; 0 = sin costo.
            $table->decimal('extra_price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Ingredientes que el producto lleva por defecto.
        Schema::create('ingredient_product', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            // Un ingrediente asignado a algún producto no se puede eliminar.
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            $table->boolean('is_removable')->default(true);
            $table->primary(['product_id', 'ingredient_id']);
        });

        // Ingredientes extra que se le pueden agregar al producto.
        Schema::create('product_extras', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            $table->primary(['product_id', 'ingredient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_extras');
        Schema::dropIfExists('ingredient_product');
        Schema::dropIfExists('ingredients');
    }
};
