<?php

namespace App\Models\Usuario_Sefuridad_y_Auditoria;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class gestionarUsuariosyRoles extends Model
{
    protected $table = 'usuario';

    protected $primaryKey = 'Id_usuario';

    public $timestamps = false;

    protected $fillable = [
        'nombre_usuario',
        'correo',
        'contrasena',
        'estado',
        'fecha_creacion',
        'Id_persona',
    ];

    protected $hidden = [
        'contrasena',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(persona::class, 'Id_persona', 'Id_persona');
    }

    public function rolesLista(): array
    {
        $persona = $this->persona;

        if (!$persona) {
            return [];
        }

        $roles = [];

        if ($persona->tipo_Superadministrador) {
            $roles[] = 'Superadministrador';
        }
        if ($persona->tipo_Administrador) {
            $roles[] = 'Administrador';
        }
        if ($persona->tipo_Docente) {
            $roles[] = 'Docente';
        }
        if ($persona->tipo_Postulante) {
            $roles[] = 'Postulante';
        }

        return $roles;
    }
}
