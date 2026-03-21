<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Impacto extends Model
{
    protected $table = "impactos";
    protected $primaryKey = 'id';
    protected $fillable = [
        "x_impacto",
        "y_impacto",
        "momento_impacto",
        "eficacia",
        "efectivo",
        "id_area",
        "id_arma",
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, "id_area");
    }

    public function arma()
    {
        return $this->belongsTo(Arma::class, "id_arma");
    }
}
