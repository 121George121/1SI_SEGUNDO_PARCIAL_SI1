<?php

namespace App\Models\Gestion_Financiera;

use Illuminate\Database\Eloquent\Model;

class comprobante extends Model
{
    protected $table = 'comprobante';

    protected $primaryKey = 'Id_comprobante';

    public $timestamps = false;

    protected $fillable = [
        'nro_comprobante',
        'fecha_emision',
        'archivo',
    ];
}