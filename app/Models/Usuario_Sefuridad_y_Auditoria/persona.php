<?php

namespace App\Models\Usuario_Sefuridad_y_Auditoria;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class persona extends Model
{
    protected $table = 'persona';

    protected $primaryKey = 'Id_persona';

    public $timestamps = false;

    protected $fillable = [
        'ci',
        'nombre',
        'apellido',
        'sexo',
        'fecha_nacimiento',
        'telefono',
        'correo',
        'direccion',
        'estado',
        'tipo_Superadministrador',
        'tipo_Administrador',
        'tipo_Docente',
        'tipo_Postulante',
    ];

    protected $casts = [
        'tipo_Superadministrador' => 'boolean',
        'tipo_Administrador' => 'boolean',
        'tipo_Docente' => 'boolean',
        'tipo_Postulante' => 'boolean',
    ];

    public function usuario(): HasOne
    {
        return $this->hasOne(gestionarUsuariosyRoles::class, 'Id_persona', 'Id_persona');
    }
}
