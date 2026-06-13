<?php

namespace App\Models\Gestion_Academica;

use Illuminate\Database\Eloquent\Model;

class gestionarTurno extends Model
{
    protected $table = 'turno';

    protected $primaryKey = 'Id_turno';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
    ];
}
