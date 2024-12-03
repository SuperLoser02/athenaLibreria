<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Puntos extends Model
{
    use HasFactory;
    protected $table = 'puntos';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'nombre',
        'porcentaje',
    ]; 

    public function punto_venta()
    {
        return $this->hasOne(Punto_Venta::class, 'punto_id');
    }

}
