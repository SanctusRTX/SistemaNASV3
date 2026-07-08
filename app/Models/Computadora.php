<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Computadora extends Model
{
    protected $table = 'ss_computadoras';

    protected $fillable = [
        'nombre',
        'numero_serie',
        'codigo_inventario',
        'marca',
        'modelo',
        'tipo',
        'procesador',
        'ram',
        'almacenamiento',
        'tarjeta_grafica',
        'sistema_operativo',
        'operador',
        'cargo_operador',
        'departamento',
        'direccion_ip',
        'direccion_mac',
        'estado',
        'observaciones',
    ];
}
