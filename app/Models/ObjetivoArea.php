<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjetivoArea extends Model
{
    protected $table = 'objetivos_area';
    
    protected $fillable = [
        'id_area',
        'nombre', // <--- Nuevo
        'x_zona',
        'y_zona'
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area');
    }
}
