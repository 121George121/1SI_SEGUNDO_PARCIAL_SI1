<?php

namespace App\Models\Gestion_Academica;

use Illuminate\Database\Eloquent\Model;

class gestionarHorarios extends Model
{
    protected $table = 'horario';

    protected $primaryKey = 'Id_horario';

    public $timestamps = false;

    protected $fillable = [
        'dia',
        'hora_inicio',
        'hora_fin',
        'estado',
        'Id_turno',
    ];
}
