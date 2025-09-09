<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mantenimiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_medidor')->constrained('medidor');
            $table->enum('tipo', ['preventivo', 'correctivo', 'instalacion', 'calibracion']);
            $table->enum('cobertura', ['incluido_suscripcion', 'facturado']);
            $table->decimal('costo', 12, 2)->default(0.00);
            $table->date('fecha');
            $table->string('descripcion', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimiento');
    }
};