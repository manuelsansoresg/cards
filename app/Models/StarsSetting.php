<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StarsSetting extends Model
{
    use HasFactory;

    protected $table = 'stars_settings';
    
    // Desactivamos 'created_at' y cambiamos la convención para 'updated_at'

    protected $fillable = [
        'stars_per_dollar',
        'paypal_client_id',
        'paypal_secret',
        'paypal_mode',
        'header_title',
        'header_image',
        'mercadopago_public_key',
        'mercadopago_access_token',
        'mercadopago_mode',
    ];
}
