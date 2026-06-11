<?php

namespace App\Models\Gestion_Academica;

use Illuminate\Database\Eloquent\Model;

class gestionarModalidad extends Model
{
    protected $table = 'modalidad';

    protected $primaryKey = 'Id_modalidad';

    public $timestamps = false;

    protected $fillable = [
        'nombre_modalidad',
        'estado',
    ];
}
