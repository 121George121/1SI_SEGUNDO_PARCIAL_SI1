<?php

namespace App\Models\Gestion_Academica;

use Illuminate\Database\Eloquent\Model;

class gestionarMateriasYHorarios extends Model
{
    protected $table = 'materia';

    protected $primaryKey = 'Id_materia';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];
}