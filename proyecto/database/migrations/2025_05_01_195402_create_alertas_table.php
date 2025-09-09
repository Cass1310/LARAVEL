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
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medidor_id');
            $table->enum('tipo', ['fuga', 'consumo_alto', 'otro']);
            $table->string('mensaje');
            $table->timestamp('fecha_hora');
            $table->enum('estado', ['pendiente', 'resuelta'])->default('pendiente');
            $table->timestamps();

            $table->foreign('medidor_id')->references('id')->on('medidores')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
