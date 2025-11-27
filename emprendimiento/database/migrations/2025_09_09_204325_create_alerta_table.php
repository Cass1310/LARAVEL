<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_medidor')->constrained('medidor');
            $table->enum('tipo_alerta', ['fuga', 'consumo_brusco', 'consumo_excesivo','fuga_nocturna']);
            $table->decimal('valor_detectado', 10, 2)->nullable();
            $table->dateTime('fecha_hora');
            $table->enum('estado', ['pendiente', 'atendida', 'resuelta'])->default('pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerta');
    }
};