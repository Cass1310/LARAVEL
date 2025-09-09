<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumo_agua', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_medidor')->constrained('medidor');
            $table->dateTime('fecha_hora');
            $table->decimal('volumen', 10, 2);
            $table->enum('tipo_registro', ['transmision', 'manual'])->default('transmision');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumo_agua');
    }
};