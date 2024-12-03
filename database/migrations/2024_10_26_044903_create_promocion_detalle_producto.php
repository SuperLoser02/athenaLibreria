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
        Schema::create('promocion_detalle_producto', function (Blueprint $table) {
            $table->string('producto_codigo', 8); // Identificación del producto

            $table->unsignedSmallInteger('promocion_detalle_id'); // Identificador de la promoción

            $table->decimal('porcentaje', 8, 2)->unsigned(); // Descuento del producto

            $table->unsignedSmallInteger('cantidad')->nullable();
            
            $table->primary(['producto_codigo', 'promocion_detalle_id']);
            
            $table->foreign('promocion_detalle_id')->references('id')->on('promocion_detalles');
            
            $table->foreign('producto_codigo')->references('codigo')->on('productos');
        });
    }

    /**
     * 
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promocion_detalle_producto');
    }
};
