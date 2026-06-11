<?php

namespace App\Models\Gestion_Academica;

use Illuminate\Database\Eloquent\Model;

class enviarNotificaciones extends Model
{
    protected $table = 'notificacion';

    protected $primaryKey = 'Id_notificacion';

    public $timestamps = false;

    protected $fillable = [
        'tipo_notificacion',
        'titulo',
        'mensaje',
        'destinatario',
        'correo_destinatario',
        'fecha_envio',
        'hora_envio',
        'estado_envio',
    ];
}
