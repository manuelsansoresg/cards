<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaUpload extends Model
{
    use HasFactory;

    protected $table = 'media_uploads';
    
    // Desactivamos 'updated_at' y renombramos 'created_at'
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null; // No hay updated_at en la tabla

    protected $fillable = [
        'upload_id',
        'file_path',
        'file_type',
        'sort_order',
    ];

    /**
     * Relación: El archivo media pertenece a un upload.
     */
    public function upload()
    {
        return $this->belongsTo(Upload::class, 'upload_id');
    }
}
