<?php

namespace App\Models\Inscripcion_y_Documentacion;

use Illuminate\Database\Eloquent\Model;

class documentos extends Model
{
    protected $table = 'documento';

    protected $primaryKey = 'Id_documento';

    public $timestamps = false;

    protected $fillable = [
        'tipo_documento',
        'nombre',
        'fecha_registro',
        'destinado_a',
        'descripcion',
    ];
}