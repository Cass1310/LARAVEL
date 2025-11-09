<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mantenimiento', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'en_proceso', 'completado', 'cancelado'])->default('pendiente')->after('descripcion');
        });
    }

    public function down()
    {
        Schema::table('mantenimiento', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
