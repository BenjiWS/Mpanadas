<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Datos públicos del negocio, editables desde Ajustes. Es una sola fila.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tagline_es')->nullable();
            $table->string('tagline_en')->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('phone');
            $table->string('whatsapp')->nullable(); // solo dígitos, con código de país
            $table->string('email')->nullable();
            $table->string('instagram')->nullable(); // usuario sin @
            $table->string('facebook')->nullable();
            $table->json('hours'); // [{from: 1..7, to: 1..7, opens: "07:00", closes: "21:00"}], 1 = lunes
            $table->unsignedTinyInteger('receipt_width')->default(80); // papel de la impresora térmica, mm: 80 o 58
            $table->string('receipt_footer')->nullable(); // mensaje al pie del recibo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
