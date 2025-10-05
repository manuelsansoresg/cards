<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Reaction;
use App\Models\Upload;
use App\Models\DetalleOrden;

class ReactionController extends Controller
{
    /**
     * Listar reacciones de una tarjeta (upload) en JSON.
     */
    public function index(Upload $upload)
    {
        $reactions = $upload->reactions()->orderBy('id','desc')->get(['reaction']);
        return response()->json([
            'upload_id' => $upload->id,
            'reactions' => $reactions->pluck('reaction'),
        ]);
    }

    /**
     * Guardar o actualizar la reacción del usuario para una tarjeta.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'upload_id' => 'required|integer|exists:uploads,id',
            'reaction' => 'required|string|max:10',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'auth_required'], 401);
        }

        $upload = Upload::findOrFail($data['upload_id']);

        // Validar que el usuario tenga acceso: gratis o comprado
        $hasAccess = (bool)$upload->is_free;
        if (!$hasAccess) {
            $hasAccess = DetalleOrden::whereHas('orden', function($q) use ($user) {
                $q->where('usuario_id', $user->id);
            })->where('archivo_id', $upload->id)->exists();
        }
        if (!$hasAccess) {
            return response()->json(['error' => 'no_access'], 403);
        }

        // Crear/actualizar única reacción por usuario y por upload
        $reaction = Reaction::updateOrCreate(
            ['user_id' => $user->id, 'upload_id' => $upload->id],
            ['reaction' => $data['reaction']]
        );

        return response()->json([
            'success' => true,
            'reaction' => $reaction->reaction,
        ]);
    }
}