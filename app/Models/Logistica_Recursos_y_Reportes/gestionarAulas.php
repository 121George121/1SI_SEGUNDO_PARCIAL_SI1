<?php

namespace App\Models\Logistica_Recursos_y_Reportes;

use Illuminate\Database\Eloquent\Model;

class gestionarAulas extends Model
{
    protected $table = 'aula';
    protected $primaryKey = 'Id_aula';

    public $timestamps = false;

    protected $fillable = [
        'nro_aula',
        'capacidad',
        'ubicacion',
        'estado',
    ];
}