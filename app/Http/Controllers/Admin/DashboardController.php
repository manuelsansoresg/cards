<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Upload;
use App\Models\Categoria;
use App\Models\Orden;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => User::count(),
            'uploads' => Upload::count(),
            'categorias' => Categoria::count(),
            'ordenes' => Orden::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}