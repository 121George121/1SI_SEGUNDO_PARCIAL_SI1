<?php

namespace App\Models\Gestion_Academica;

use Illuminate\Database\Eloquent\Model;

class gestionarCarrerasYCupos extends Model
{
    protected $table = 'carrera';
    protected $primaryKey = 'id_carrera';

    public $timestamps = false;

    protected $fillable = [
        'nombre_carrera',
        'descripcion',
        'estado',
    ];
}