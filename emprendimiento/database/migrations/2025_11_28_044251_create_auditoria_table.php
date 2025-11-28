<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable(); // Guardar ID como string por si se elimina el usuario
            $table->string('user_nombre')->nullable(); // Guardar nombre por separado
            $table->string('user_email')->nullable(); // Guardar email por separado
            $table->string('user_rol')->nullable(); // Guardar rol por separado
            $table->string('accion'); // login, logout, create, update, delete, etc.
            $table->string('modulo')->nullable(); // usuarios, edificios, medidores, etc.
            $table->text('descripcion')->nullable();
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->json('datos_anteriores')->nullable(); // Para updates/deletes
            $table->json('datos_nuevos')->nullable(); // Para creates/updates
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};