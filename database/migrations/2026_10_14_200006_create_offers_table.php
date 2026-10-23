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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable(); // ruta en el disco public
            // App\Enums\DiscountType; two_for_one (2 × 1) no usa discount_value.
            $table->enum('discount_type', ['percentage', 'fixed', 'two_for_one']);
            $table->decimal('discount_value', 8, 2);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            // Horario dentro de la vigencia: días ISO separados por coma (1 = lunes ... 7 = domingo)
            // y franja horaria. Null = todos los días / todo el día.
            $table->string('weekdays', 13)->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Una oferta puede aplicar a productos, categorías o combos.
        Schema::create('offers_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('offers_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('offers_combos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete();
            $table->foreignId('combo_id')->constrained('combos')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers_combos');
        Schema::dropIfExists('offers_categories');
        Schema::dropIfExists('offers_products');
        Schema::dropIfExists('offers');
    }
};
