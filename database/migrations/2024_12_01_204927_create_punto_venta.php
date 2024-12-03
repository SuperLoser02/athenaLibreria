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
        Schema::create('punto_venta', function (Blueprint $table) {
            $table->unsignedInteger('venta_nro');
            $table->unsignedSmallInteger('punto_id');
            $table->unsignedTinyInteger('porcentaje_rebaja');

            $table->primary(['venta_nro', 'punto_id']);

            $table->foreign('venta_nro')->references('nro')->on('ventas');
            $table->foreign('punto_id')->references('id')->on('puntos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_punto_venta');
    }
};
