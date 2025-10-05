<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();
        if (($user->role ?? null) === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // Usuarios no-admin no tienen home, redirigir a raíz
        return redirect('/');
    }
}
