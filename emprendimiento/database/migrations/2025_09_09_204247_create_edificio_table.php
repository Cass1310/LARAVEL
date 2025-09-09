<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edificio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_propietario')->constrained('users');
            $table->string('nombre', 100)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edificio');
    }
};