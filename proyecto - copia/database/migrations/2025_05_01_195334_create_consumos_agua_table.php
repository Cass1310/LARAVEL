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
        Schema::create('consumos_agua', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medidor_id');
            $table->timestamp('fecha_hora');
            $table->float('litros');
            $table->float('caudal')->nullable();         // Litros por minuto
            $table->float('voltaje_bateria')->nullable();
            $table->timestamps();

            $table->foreign('medidor_id')->references('id')->on('medidores')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumos_agua');
    }
};
