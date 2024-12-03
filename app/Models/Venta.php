<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';
    protected $primaryKey = 'nro';
    public $timestamps = false;
    use HasFactory;
    protected $fillable = [
        'nro',
        'monto_total',
    ];
    public function factura()
    {
        return $this->hasOne(Factura::class,'venta_nro','nro');
    }
    public function venta_Producto()
    {
        return $this->hasOne(Venta_Producto::class);
    }
    public function punto_venta()
    {
        return $this->hasOne(Punto_Venta::class, 'venta_nro');
    }
}
