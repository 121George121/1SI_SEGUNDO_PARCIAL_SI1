<?php

namespace App\Models\Logistica_Recursos_y_Reportes;

use Illuminate\Database\Eloquent\Model;

class gestionarEspecialidad extends Model
{
    protected $table = 'especialidad';
    protected $primaryKey = 'Id_especialidad';

    public $timestamps = false;

    protected $fillable = [
        'nombre_especialidad',
        'id_materia',
    ];
}
