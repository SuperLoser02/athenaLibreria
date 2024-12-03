<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Punto_Venta extends Model
{
    use HasFactory;
    protected $table = 'punto_venta';
    public $timestamps = false;

    protected $fillable = [
        'venta_nro',
        'punto_id',
        'porcentaje_rebaja',
    ]; 

    public function puntos()
    {
        return $this->hasOne(Puntos::class, 'punto_id');
    }

    public function ventas()
    {
        return $this->hasOne(Venta::class, 'venta_nro');
    }
}
