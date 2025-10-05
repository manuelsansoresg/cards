<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Upload;
use App\Models\Orden;
use App\Models\DetalleOrden;
use App\Models\StarsSetting;

class CheckoutController extends Controller
{
    public function page()
    {
        return view('front.checkout');
    }

    public function checkout(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Debe iniciar sesión'], 401);
        }

        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:uploads,id',
            'items.*.cantidad' => 'nullable|integer|min:1',
            'metodo' => 'required|string|in:paypal,mercadopago',
        ]);

        $starsCfg = StarsSetting::first();
        $starsPerDollar = $starsCfg->stars_per_dollar ?? 1;

        $uploads = Upload::whereIn('id', collect($data['items'])->pluck('id'))->get()->keyBy('id');
        $totalStars = 0;

        foreach ($data['items'] as $it) {
            $upload = $uploads[$it['id']] ?? null;
            if (!$upload) continue;
            $qty = max(1, (int)($it['cantidad'] ?? 1));
            $totalStars += ($upload->stars_cost ?? 0) * $qty;
        }

        $usd = $totalStars / max(1, $starsPerDollar);

        // Simular integración de pago basada en método seleccionado
        $paid = false;
        if ($data['metodo'] === 'paypal') {
            $paid = $this->processPayPal($usd);
        } elseif ($data['metodo'] === 'mercadopago') {
            $paid = $this->processMercadoPago($usd);
        }
        if (!$paid) {
            return response()->json(['success' => false, 'message' => 'No se pudo confirmar el pago']);
        }

        // En este ejemplo marcamos como pagado directamente.
        // Integraciones reales con PayPal/MercadoPago pueden actualizar 'estado'.
        return DB::transaction(function () use ($user, $data, $uploads, $totalStars, $usd) {
            $orden = Orden::create([
                'usuario_id' => $user->id,
                'transaccion_id' => 'TX-'.uniqid(),
                'total_monto' => $totalStars,
                'estado' => 'aprobado',
                'metodo_pago' => $data['metodo'],
                'email' => $user->email,
            ]);

            foreach ($data['items'] as $it) {
                $upload = $uploads[$it['id']] ?? null;
                if (!$upload) continue;
                $qty = max(1, (int)($it['cantidad'] ?? 1));
                DetalleOrden::create([
                    'orden_id' => $orden->id,
                    'archivo_id' => $upload->id,
                    'precio_unitario' => $upload->stars_cost ?? 0,
                    'cantidad' => $qty,
                ]);
            }

            return response()->json([
                'success' => true,
                'orden_id' => $orden->id,
                'total_stars' => $totalStars,
                'total_usd' => $usd,
            ]);
        });
    }

    protected function processPayPal(float $amountUsd): bool
    {
        $cfg = StarsSetting::first();
        // Aquí iría la llamada a la API de PayPal usando $cfg->paypal_client_id, $cfg->paypal_secret, $cfg->paypal_mode
        // Por ahora, devolvemos true para simular éxito
        return $amountUsd >= 0; 
    }

    protected function processMercadoPago(float $amountUsd): bool
    {
        $cfg = StarsSetting::first();
        // Aquí iría la llamada a la API de Mercado Pago usando $cfg->mercadopago_public_key, $cfg->mercadopago_access_token, $cfg->mercadopago_mode
        // Por ahora, devolvemos true para simular éxito
        return $amountUsd >= 0;
    }
}