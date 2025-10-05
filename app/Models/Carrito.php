<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;

    protected $table = 'carrito';
    
    protected $fillable = [
        'usuario_id',
        'archivo_id',
        'cantidad',
    ];

    /**
     * Relación: El item de carrito pertenece a un usuario.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Relación: El item de carrito referencia a un archivo (upload).
     */
    public function archivo()
    {
        return $this->belongsTo(Upload::class, 'archivo_id');
    }
}
