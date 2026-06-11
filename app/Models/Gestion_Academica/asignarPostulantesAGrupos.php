<?php

namespace App\Models\Gestion_Academica;

use Illuminate\Database\Eloquent\Model;

class asignarPostulantesAGrupos extends Model
{
    protected $table = 'grupo_postulante';

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'Id_grupo',
        'Id_postulante',
        'fecha_asignacion',
        'estado',
    ];
}
