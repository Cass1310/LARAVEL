<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumo_departamento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_consumo')->constrained('consumo_edificio');
            $table->foreignId('id_departamento')->constrained('departamento');
            $table->decimal('monto_asignado', 12, 2);
            $table->decimal('consumo_m3', 10, 2);
            $table->decimal('porcentaje_consumo', 5, 2); // % relativo
            $table->enum('estado', ['pendiente', 'pagado', 'vencido'])->default('pendiente');
            $table->date('fecha_pago')->nullable();
            $table->timestamps();

            $table->unique(['id_consumo', 'id_departamento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumo_departamento');
    }
};