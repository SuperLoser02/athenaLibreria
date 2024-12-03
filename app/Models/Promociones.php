<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promociones extends Model
{
    use HasFactory;
    protected $table = 'promociones';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'descripcion',
    ];

    public function promocion_detalle()
    {
     return $this->hasMany(Promocion_detalles::class, 'promociones_id');
    }
}
