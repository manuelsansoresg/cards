<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    use HasFactory;

    protected $table = 'ordenes'; // Nombre de la tabla

    protected $fillable = [
        'usuario_id',
        'transaccion_id',
        'total_monto',
        'estado',
        'metodo_pago',
        'email',
    ];
    
    /**
     * Relación: Una orden pertenece a un usuario.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Relación: Una orden tiene muchos detalles (los archivos comprados).
     */
    public function detalles()
    {
        return $this->hasMany(DetalleOrden::class, 'orden_id');
    }
}
