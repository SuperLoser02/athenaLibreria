<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocion_detalles extends Model
{
    use HasFactory;
    protected $table = 'promocion_detalles';

    public $timestamps = false;

    protected $fillable=[
        'id',
        'nombre',
        'descripcion',
        'fecha_inicio',
        'fecha_final',
        'promocione_id',
    ];
    
    public function promocion()
    {
     return $this->belongsTo(Promociones::class, 'promociones_id');
    }
    
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'promocion_detalle_producto', 'promocion_detalle_id', 'producto_codigo')
                    ->withPivot('porcentaje', 'cantidad');
    }
   
}
