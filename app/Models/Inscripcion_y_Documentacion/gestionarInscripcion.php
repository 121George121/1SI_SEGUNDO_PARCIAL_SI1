<?php

namespace App\Models\Inscripcion_y_Documentacion;

use Illuminate\Database\Eloquent\Model;

class gestionarInscripcion extends Model
{
    protected $table = 'inscripcion';
    protected $primaryKey = 'Codigo_inscripcion';

    public $timestamps = false;

    protected $fillable = [
        'estado',
        'fecha_inscripcion',
        'Id_postulante',
    ];
}