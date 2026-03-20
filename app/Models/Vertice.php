<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vertice extends Model
{
    protected $table = "vertices_area";
    protected $primaryKey = 'id';
    protected $fillable = [
        "x",
        "y",
        "id_area",
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, "id_area");
    }
}
