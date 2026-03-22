<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigMapa extends Model
{
    protected $table = "config_mapa";
    protected $primaryKey = 'id';
    protected $fillable = [
        "huso",
        "sistemaCoordenadas",
        "hemisferio",
        "prefijo_nombre_boca",
        "numero_boca_inicial",
    ];

    protected $casts = [
        "hemisferio" => "boolean",
        "numero_boca_inicial" => "integer",
    ];

}
