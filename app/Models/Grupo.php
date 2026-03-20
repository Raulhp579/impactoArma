<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $table = "grupos";
    protected $primaryKey = 'id';
    protected $fillable = [
        "nombre",
        "descripcion",
    ];

    public function armas()
    {
        return $this->hasMany(Arma::class, "id_grupo");
    }
}
