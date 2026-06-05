<?php

namespace App\Models\Logistica_Recursos_y_Reportes;

use Illuminate\Database\Eloquent\Model;

class gestionarDocentes extends Model
{
    protected $table = 'docente';
    protected $primaryKey = 'Id_docente';

    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'Id_docente',
        'anio_servicio',
        'estado',
    ];
}