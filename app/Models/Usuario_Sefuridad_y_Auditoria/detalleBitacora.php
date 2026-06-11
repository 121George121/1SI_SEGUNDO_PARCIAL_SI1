<?php

namespace App\Models\Usuario_Sefuridad_y_Auditoria;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class detalleBitacora extends Model
{
    protected $table = 'detalle_bitacora';

    protected $primaryKey = 'Id_detallebitacora';

    public $timestamps = false;

    protected $fillable = [
        'direccion_ip',
        'hora_inicio',
        'hora_fin',
        'accion',
        'Id_bitacora',
    ];

    public function bitacora(): BelongsTo
    {
        return $this->belongsTo(gestionarBitacora::class, 'Id_bitacora', 'Id_bitacora');
    }
}
