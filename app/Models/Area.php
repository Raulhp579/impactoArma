<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = "areas";
    protected $primaryKey = 'id';
    protected $fillable = [
        "nombre",
        "x_objetivo",
        "y_objetivo",
    ];
    public function vertices()
    {
        return $this->hasMany(Vertice::class, 'id_area');
    }
}
  