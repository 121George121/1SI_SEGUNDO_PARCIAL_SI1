<?php

namespace App\Models\Logistica_Recursos_y_Reportes;

use Illuminate\Database\Eloquent\Model;

class reporte extends Model
{
    protected $table = 'reporte';
    protected $primaryKey = 'Id_reporte';
    public $timestamps = false;

    protected $fillable = [
        'tipo_reporte',
        'fecha_generacion',
        'descripcion',
        'filtro_usado',
        'Id_usuario',
    ];
}
