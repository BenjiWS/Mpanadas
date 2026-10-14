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
        // Todos los montos están en USD.
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('payment_method'); // App\Enums\PaymentMethod
            $table->string('status')->default('completed'); // App\Enums\SaleStatus
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('extras_total', 10, 2)->default(0);
            $table->decimal('discount_total', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            // Solo en pagos en efectivo.
            $table->decimal('amount_received', 10, 2)->nullable();
            $table->decimal('change_amount', 10, 2)->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('offer_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('quantity');
            // Valores por unidad, congelados al momento de la venta.
            $table->decimal('unit_price', 10, 2);
            $table->decimal('extras_price', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            // (unit_price + extras_price - discount) * quantity
            $table->decimal('subtotal', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Ingredientes quitados o agregados en cada línea de venta.
        Schema::create('sale_item_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            $table->string('type'); // App\Enums\IngredientChange
            // Precio del extra al momento de la venta; 0 si se quitó.
            $table->decimal('price', 10, 2)->default(0);

            $table->unique(['sale_item_id', 'ingredient_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_item_ingredients');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
