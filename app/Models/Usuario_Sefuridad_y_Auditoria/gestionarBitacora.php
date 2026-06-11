<?php

namespace App\Models\Usuario_Sefuridad_y_Auditoria;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class gestionarBitacora extends Model
{
    protected $table = 'bitacora';

    protected $primaryKey = 'Id_bitacora';

    public $timestamps = false;

    protected $fillable = [
        'tipo',
        'descripcion',
        'fecha',
        'hora',
        'estado',
        'Id_usuario',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(autenticacion::class, 'Id_usuario', 'Id_usuario');
    }

    public function detalle(): HasOne
    {
        return $this->hasOne(detalleBitacora::class, 'Id_bitacora', 'Id_bitacora');
    }
}
