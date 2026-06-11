<?php

namespace App\Models\Gestion_Academica;

use Illuminate\Database\Eloquent\Model;

class gestionarGestion extends Model
{
    protected $table = 'gestion';

    protected $primaryKey = 'Id_gestion';

    public $timestamps = false;

    protected $fillable = [
        'anio',
        'periodo',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];
}
