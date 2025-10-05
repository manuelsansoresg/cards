<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleOrden extends Model
{
    use HasFactory;

    protected $table = 'detalles_orden';

    // Desactivamos updated_at y usamos 'creado_en' para created_at
    public $timestamps = true;
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null; 

    protected $fillable = [
        'orden_id',
        'archivo_id',
        'precio_unitario',
        'cantidad',
    ];

    /**
     * Relación: Un detalle pertenece a una orden.
     */
    public function orden()
    {
        return $this->belongsTo(Orden::class, 'orden_id');
    }

    /**
     * Relación: Un detalle se refiere a un archivo (upload) comprado.
     */
    public function archivo()
    {
        return $this->belongsTo(Upload::class, 'archivo_id');
    }
}
