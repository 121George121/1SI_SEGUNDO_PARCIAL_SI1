<?php

namespace App\Models\Inscripcion_y_Documentacion;

use Illuminate\Database\Eloquent\Model;

class gestionarPreferencia extends Model
{
    protected $table = 'preferencia_inscripcion';

    protected $primaryKey = 'Id_preferencia';

    public $timestamps = false;

    protected $fillable = [
        'Codigo_inscripcion',
        'Id_modalidad',
        'Id_turno',
        'estado',
    ];

    public function inscripcion()
    {
        return $this->belongsTo(gestionarInscripcion::class, 'Codigo_inscripcion', 'Codigo_inscripcion');
    }
}
