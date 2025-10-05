<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    // Indica el nombre de la tabla si es diferente de la convención de Laravel
    protected $table = 'categorias'; 

    // Indica que no hay updated_at, aunque la migración estándar lo incluye
    // public $timestamps = false; 

    protected $fillable = [
        'nombre',
        'estado',
    ];

    /**
     * Relación: Una categoría tiene muchos uploads.
     */
    public function uploads()
    {
        return $this->hasMany(Upload::class, 'categoria_id');
    }
}
