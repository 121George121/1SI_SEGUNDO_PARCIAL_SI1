<?php

namespace App\Models\Gestion_Academica;

use Illuminate\Database\Eloquent\Model;

class gestionarEvaluacionesYNotas extends Model
{
    protected $table = 'evaluacion';

    protected $primaryKey = 'Id_evaluacion';

    public $timestamps = false;

    protected $fillable = [
        'numero_evaluacion',
        'porcentaje',
        'fecha',
        'estado',
        'Id_grupo',
        'Id_materia',
    ];
}