<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;

    protected $table = 'uploads';

    
    // Indicamos que 'created_at' no debe ser manejado por Eloquent
    const CREATED_AT = 'created_at';
    
    protected $fillable = [
        'user_id',
        'title',
        'price',
        'stars_cost',
        'is_free',
        'type',
        'categoria_id',
    ];

    // Relaciones:
    
    /**
     * Relación: Un upload pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación: Un upload pertenece a una categoría.
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Relación: Un upload puede tener múltiples archivos adjuntos (media_uploads).
     */
    public function media()
    {
        return $this->hasMany(MediaUpload::class, 'upload_id');
    }

    /**
     * Relación: Un upload puede tener múltiples reacciones (emojis).
     */
    public function reactions()
    {
        return $this->hasMany(Reaction::class, 'upload_id');
    }
}
