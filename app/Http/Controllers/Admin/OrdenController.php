<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use App\Models\DetalleOrden;
use Illuminate\Http\Request;

class OrdenController extends Controller
{
    public function index(Request $request)
    {
        $query = Orden::query();

        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->input('desde'));
        }
        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->input('hasta'));
        }
        if ($request->filled('metodo_pago')) {
            $query->where('metodo_pago', $request->input('metodo_pago'));
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        $ordenes = $query->orderBy('id','desc')->paginate(20);

        $ganancias = $query->sum('total_monto');

        return view('admin.ordenes.index', compact('ordenes','ganancias'));
    }
}