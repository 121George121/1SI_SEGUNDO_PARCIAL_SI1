<?php

namespace App\Models\Gestion_Academica;

use Illuminate\Database\Eloquent\Model;

class asignarDocentesAGruposYMaterias extends Model
{
    protected $table = 'grupo_materia';

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'Id_grupo',
        'Id_materia',
        'Id_docente',
    ];
}