<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intercambios extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'intercambios';
    protected $fillable =[
        'nro',
        'fecha',
        'motivo',
        'user_id',
        'cliente_ci'
    ];

    public function productos(){
        return $this->belongsToMany(Producto::class, 'intercambios_producto','intercambio_nro','producto_codigo')
        ->withPivot('cantidad');;
    }
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function cliente(){
        return $this->belongsTo(Cliente::class, 'cliente_ci');
    }
}
