<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Categoria;
use App\Models\Upload;
use App\Models\MediaUpload;

class DemoContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegurar usuario admin para asignar los uploads
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $admin = User::updateOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'name' => 'Admin',
                    'password' => Hash::make('demo123'),
                    'role' => 'admin',
                    'stars' => 0,
                    'activo' => true,
                ]
            );
        }

        // Crear categoría demo
        $categoria = Categoria::firstOrCreate(
            ['nombre' => 'General'],
            ['estado' => 'activo']
        );

        // Crear un upload demo
        $upload = Upload::firstOrCreate(
            ['title' => 'Archivo demo'],
            [
                'user_id' => $admin->id,
                'price' => 1.50,
                'stars_cost' => 30,
                'is_free' => false,
                'type' => 'image',
                'categoria_id' => $categoria->id,
            ]
        );

        // Asociar un media demo (usando imagen existente en public/images)
        MediaUpload::firstOrCreate(
            ['upload_id' => $upload->id, 'file_path' => 'images/telegram.jpg'],
            [
                'file_type' => 'image',
                'sort_order' => 0,
            ]
        );
    }
}