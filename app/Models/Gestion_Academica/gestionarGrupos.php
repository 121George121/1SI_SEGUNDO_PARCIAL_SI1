<?php

namespace App\Models\Gestion_Academica;

use Illuminate\Database\Eloquent\Model;

class gestionarGrupos extends Model
{
    protected $table = 'grupo';

    protected $primaryKey = 'Id_grupo';

    public $timestamps = false;

    protected $fillable = [
        'sigla_grupo',
        'capacidad_max',
        'estado',
        'cant_estudiantes',
        'Id_aula',
        'Id_modalidad',
        'Id_turno',
        'Id_gestion',
    ];
}