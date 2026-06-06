<?php

namespace App\Models\Gestion_Financiera;

use Illuminate\Database\Eloquent\Model;

class gestionarPagos extends Model
{
    protected $table = 'pago';
    protected $primaryKey = 'Id_pago';

    public $timestamps = false;

    protected $fillable = [
        'concepto_pago',
        'monto',
        'estado_pago',
        'observaciones',
    ];
}