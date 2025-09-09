<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gateway', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_gateway', 50)->unique();
            $table->string('descripcion', 200)->nullable();
            $table->string('ubicacion', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway');
    }
};