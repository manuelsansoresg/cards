<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    use HasFactory;

    protected $table = 'reactions'; 

    protected $fillable = [
        'user_id',
        'upload_id', // Usamos upload_id como se definió en la migración
        'reaction',
    ];

    /**
     * Relación: La reacción pertenece a un usuario (User).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: La reacción pertenece a un archivo subido (Upload).
     */
    public function upload()
    {
        return $this->belongsTo(Upload::class);
    }
}
