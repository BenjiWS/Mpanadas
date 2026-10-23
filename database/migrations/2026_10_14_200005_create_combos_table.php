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
        Schema::create('combos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable(); // ruta en el disco public
            $table->decimal('price', 8, 2);
            $table->boolean('is_active')->default(true); // permite sacar un combo de la carta sin borrarlo
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('combos_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_id')->constrained('combos')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedSmallInteger('quantity')->default(1); // unidades de ese producto (ej. 3 empanadas de carne)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combos_products');
        Schema::dropIfExists('combos');
    }
};
