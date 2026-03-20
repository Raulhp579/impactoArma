<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arma extends Model
{
    protected $table = "armas";
    protected $primaryKey = 'id';
    protected $fillable = [
        "tipo",
        "nombre",
        "descripcion",
        "cord_x",
        "cord_y",
        "id_grupo",
    ];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, "id_grupo");
    }

    public function impactos()
    {
        return $this->hasMany(Impacto::class, "id_arma");
    }
    
}
