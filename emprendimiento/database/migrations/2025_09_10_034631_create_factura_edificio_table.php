<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura_edificio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_edificio')->constrained('edificio');
            $table->string('periodo', 20); // ej: '2025-08'
            $table->decimal('monto_total', 12, 2);
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->enum('estado', ['pendiente', 'pagada', 'vencida'])->default('pendiente');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['id_edificio', 'periodo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_edificio');
    }
};