<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medidor', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_lorawan', 100)->unique();
            
            $table->foreignId('id_departamento')->constrained('departamento');
            $table->foreignId('id_gateway')->constrained('gateway');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->date('fecha_instalacion')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medidor');
    }
};