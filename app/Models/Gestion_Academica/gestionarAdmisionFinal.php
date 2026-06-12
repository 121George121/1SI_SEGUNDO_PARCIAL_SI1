<?php

namespace App\Models\Gestion_Academica;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class gestionarAdmisionFinal extends Model
{
    protected $table = 'asignacioncupo';
    protected $primaryKey = 'Id_asignacioncupo';

    public $timestamps = false;

    protected $fillable = [
        'fecha_asignacion',
        'promedio_final',
        'puesto_merito',
        'estado_asignacion',
        'Id_carrera',
        'Id_gestion',
    ];

    /**
     * Relación con el modelo Carrera.
     */
    public function carrera(): BelongsTo
    {
        return $this->belongsTo(gestionarCarrerasYCupos::class, 'Id_carrera', 'Id_carrera');
    }

    /**
     * Relación con el modelo Gestion.
     */
    public function gestion(): BelongsTo
    {
        return $this->belongsTo(gestionarGestion::class, 'Id_gestion', 'Id_gestion');
    }
}
