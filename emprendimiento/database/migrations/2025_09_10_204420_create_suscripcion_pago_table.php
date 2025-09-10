<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suscripcion_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_suscripcion')->constrained('suscripcion');
            $table->string('periodo', 20);
            $table->decimal('monto', 12, 2);
            $table->enum('estado', ['pendiente', 'pagado', 'vencido'])->default('pendiente');
            $table->date('fecha_pago')->nullable();
            $table->timestamps();

            $table->index('id_suscripcion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suscripcion_pago');
    }
};