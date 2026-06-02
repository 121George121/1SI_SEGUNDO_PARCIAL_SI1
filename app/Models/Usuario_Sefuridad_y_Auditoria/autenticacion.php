<?php

namespace App\Models\Usuario_Sefuridad_y_Auditoria;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class autenticacion extends Authenticatable
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

    public function getAuthPassword(): string
    {
        return $this->contrasena;
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(persona::class, 'Id_persona', 'Id_persona');
    }
}
