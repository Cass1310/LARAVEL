<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residente_departamento', function (Blueprint $table) {
            $table->foreignId('id_residente')->constrained('users');
            $table->foreignId('id_departamento')->constrained('departamento');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->timestamps();
            
            $table->primary(['id_residente', 'id_departamento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residente_departamento');
    }
};