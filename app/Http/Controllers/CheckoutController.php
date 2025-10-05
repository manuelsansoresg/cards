<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Upload;
use App\Models\Orden;
use App\Models\DetalleOrden;
use App\Models\StarsSetting;
use GuzzleHttp\Client as GuzzleClient;
use MercadoPago\SDK as MPSDK;
use MercadoPago\Preference as MPPreference;
use MercadoPago\Item as MPItem;

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

        $usd = round($totalStars / max(1, $starsPerDollar), 2);

        // Crear integración real: generar redirección según método
        $redirect = null;
        $transactionId = null;

        if ($data['metodo'] === 'paypal') {
            $paypal = $this->processPayPal(
                $usd,
                route('paypal.return'),
                url('/pago'),
                $data['items']
            );
            if (!$paypal || empty($paypal['approval_url']) || empty($paypal['order_id'])) {
                $msg = is_array($paypal) && isset($paypal['error']) ? ('PayPal: '.$paypal['error']) : 'No se pudo iniciar el pago con PayPal';
                return response()->json(['success' => false, 'message' => $msg]);
            }
            $redirect = $paypal['approval_url'];
            $transactionId = $paypal['order_id'];
        } elseif ($data['metodo'] === 'mercadopago') {
            $mp = $this->processMercadoPago(
                $usd,
                route('mp.success'),
                route('mp.failure'),
                $data['items']
            );
            if (!$mp || empty($mp['init_point']) || empty($mp['preference_id'])) {
                $msg = is_array($mp) && isset($mp['error']) ? ('Mercado Pago: '.$mp['error']) : 'No se pudo iniciar el pago con Mercado Pago';
                return response()->json(['success' => false, 'message' => $msg]);
            }
            $redirect = $mp['init_point'];
            $transactionId = $mp['preference_id'];
        }

        if (!$redirect) {
            return response()->json(['success' => false, 'message' => 'No se pudo preparar el pago']);
        }

        // Crear orden pendiente (sin detalles). Los detalles se crean tras confirmar el pago.
        $orden = Orden::create([
            'usuario_id' => $user->id,
            'transaccion_id' => $transactionId,
            'total_monto' => $totalStars,
            'estado' => 'pendiente',
            'metodo_pago' => $data['metodo'],
            'email' => $user->email,
        ]);

        // Guardar items en sesión vinculados a la transacción
        $sessionKey = 'order_items_'.$transactionId;
        session([$sessionKey => $data['items']]);

        return response()->json([
            'success' => true,
            'redirect' => $redirect,
            'orden_id' => $orden->id,
        ]);
    }

    protected function processPayPal(float $amountUsd, string $returnUrl, string $cancelUrl, array $items): ?array
    {
        $cfg = StarsSetting::first();
        $clientId = trim((string)$cfg->paypal_client_id);
        $secret = trim((string)$cfg->paypal_secret);
        if (!$clientId || !$secret) { return ['error' => 'Faltan credenciales (client_id/secret)']; }
        // Usar REST directamente (sin SDK) para crear la orden
        try {
            $base = (strtolower(trim((string)$cfg->paypal_mode)) === 'live') ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
            // Obtener access token
            $ch = curl_init($base.'/v1/oauth2/token');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_USERPWD => $clientId.':'.$secret,
                CURLOPT_POSTFIELDS => http_build_query(['grant_type' => 'client_credentials'])
            ]);
            $tokenResp = curl_exec($ch);
            curl_close($ch);
            $tokenData = json_decode($tokenResp, true);
            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) { return ['error' => 'No se pudo obtener access_token de PayPal']; }

            // Crear orden
            $http = new GuzzleClient(['base_uri' => $base]);
            $rest = $http->post('/v2/checkout/orders', [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [[
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => number_format($amountUsd, 2, '.', '')
                        ],
                        'description' => 'Compra de archivos digitales',
                    ]],
                    'application_context' => [
                        'return_url' => $returnUrl,
                        'cancel_url' => $cancelUrl,
                    ],
                ],
                'http_errors' => false,
            ]);
            $status = $rest->getStatusCode();
            $data = json_decode((string)$rest->getBody(), true);
            if ($status < 200 || $status >= 300) {
                \Log::error('PayPal OrdersCreate fallo', ['status' => $status, 'response' => $data]);
                return ['error' => 'Error al crear la orden en PayPal'];
            }
            $approval = null;
            foreach (($data['links'] ?? []) as $ln) {
                if (($ln['rel'] ?? '') === 'approve') { $approval = $ln['href']; break; }
            }
            if (!$approval || empty($data['id'])) {
                \Log::error('PayPal REST orders response sin approval/id', ['response' => $data]);
                return ['error' => 'Orden creada sin approval/id'];
            }
            return [
                'approval_url' => $approval,
                'order_id' => $data['id'],
            ];
        } catch (\Throwable $e) {
            \Log::error('PayPal REST OrdersCreate error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ['error' => $e->getMessage()];
        }
    }

    protected function processMercadoPago(float $amountUsd, string $successUrl, string $failureUrl, array $items): ?array
    {
        $cfg = StarsSetting::first();
        $token = trim((string)$cfg->mercadopago_access_token);
        if (!$token) { return ['error' => 'Falta Access Token de Mercado Pago']; }
        try {
            MPSDK::setAccessToken($token);
            $preference = new MPPreference();
            $item = new MPItem();
            $item->title = 'Compra de archivos digitales';
            $item->quantity = 1;
            $item->unit_price = floatval(number_format($amountUsd, 2, '.', ''));
            // No fijamos currency_id para usar la moneda de la cuenta
            $preference->items = [$item];
            $preference->back_urls = [
                'success' => $successUrl,
                'failure' => $failureUrl,
                'pending' => $successUrl,
            ];
            $preference->auto_return = 'approved';
            $preference->external_reference = 'TX-'.uniqid();
            $preference->save();

            $init = $preference->init_point ?? $preference->sandbox_init_point ?? null;
            $id = $preference->id ?? null;
            // Si el SDK no devuelve init_point/id, intentamos vía REST con Guzzle como fallback
            if (!$init || !$id) {
                try {
                    $http = new GuzzleClient(['base_uri' => 'https://api.mercadopago.com']);
                    $resp = $http->post('/checkout/preferences', [
                        'headers' => [
                            'Authorization' => 'Bearer '.$token,
                            'Content-Type' => 'application/json',
                        ],
                        'json' => [
                            'items' => [[
                                'title' => 'Compra de archivos digitales',
                                'quantity' => 1,
                                'unit_price' => floatval(number_format($amountUsd, 2, '.', '')),
                            ]],
                            'back_urls' => [
                                'success' => $successUrl,
                                'failure' => $failureUrl,
                                'pending' => $successUrl,
                            ],
                            'auto_return' => 'approved',
                            'external_reference' => 'TX-'.uniqid(),
                        ],
                    ]);
                    $data = json_decode((string)$resp->getBody(), true);
                    $init = $data['sandbox_init_point'] ?? $data['init_point'] ?? null;
                    $id = $data['id'] ?? null;
                    if (!$init || !$id) {
                        \Log::error('MercadoPago REST preferences response sin init_point/id', ['response' => $data]);
                        return ['error' => 'Preference creada sin init_point/id'];
                    }
                } catch (\Throwable $e) {
                    \Log::error('MercadoPago REST error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
                    return ['error' => $e->getMessage()];
                }
            }
            return [
                'init_point' => $init,
                'preference_id' => $id,
            ];
        } catch (\Throwable $e) {
            \Log::error('MercadoPago Preference error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ['error' => $e->getMessage()];
        }
    }

    // Callback: PayPal return (captura de orden)
    public function paypalReturn(Request $request)
    {
        $orderId = $request->query('token'); // PayPal envía 'token' con el id de la orden
        if (!$orderId) { return redirect('/pago')->with('error','Token de PayPal inválido'); }

        $cfg = StarsSetting::first();
        $base = ($cfg && strtolower($cfg->paypal_mode) === 'live') ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
        // Obtener access token
        $ch = curl_init($base.'/v1/oauth2/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => $cfg->paypal_client_id.':'.$cfg->paypal_secret,
            CURLOPT_POSTFIELDS => http_build_query(['grant_type' => 'client_credentials'])
        ]);
        $tokenResp = curl_exec($ch);
        curl_close($ch);
        $tokenData = json_decode($tokenResp, true);
        $accessToken = $tokenData['access_token'] ?? null;
        if (!$accessToken) { return redirect('/pago')->with('error','No se pudo autenticar con PayPal'); }

        // Capturar orden vía REST (sin SDK)
        try {
            $http = new GuzzleClient(['base_uri' => $base]);
            $cap = $http->post('/v2/checkout/orders/'.$orderId.'/capture', [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => new \stdClass(),
                'http_errors' => false,
            ]);
            $statusCode = $cap->getStatusCode();
            $capData = json_decode((string)$cap->getBody(), true);
            $status = $capData['status'] ?? null;
            if ($statusCode < 200 || $statusCode >= 300) {
                \Log::error('PayPal capture fallo', ['status' => $statusCode, 'response' => $capData]);
            }
        } catch (\Throwable $e) {
            \Log::error('PayPal capture error: '.$e->getMessage());
            $status = null;
        }
        if ($status !== 'COMPLETED') { return redirect('/pago')->with('error','El pago PayPal no se completó'); }

        // Finalizar orden: crear detalles y marcar aprobada
        $orden = Orden::where('transaccion_id', $orderId)->where('usuario_id', optional($request->user())->id)->first();
        if (!$orden) { return redirect('/')->with('error','Orden no encontrada'); }
        $items = session('order_items_'.$orderId, []);
        $uploads = Upload::whereIn('id', collect($items)->pluck('id'))->get()->keyBy('id');
        DB::transaction(function() use ($orden, $items, $uploads) {
            foreach ($items as $it) {
                $upload = $uploads[$it['id']] ?? null;
                if (!$upload) { continue; }
                $qty = max(1, (int)($it['cantidad'] ?? 1));
                DetalleOrden::create([
                    'orden_id' => $orden->id,
                    'archivo_id' => $upload->id,
                    'precio_unitario' => $upload->stars_cost ?? 0,
                    'cantidad' => $qty,
                ]);
            }
            $orden->estado = 'aprobado';
            $orden->save();
        });
        // Limpiar sesión
        session()->forget('order_items_'.$orderId);

        return redirect('/')->with('success','Pago confirmado. ¡Gracias!');
    }

    // Callbacks de MercadoPago
    public function mpSuccess(Request $request)
    {
        $status = $request->query('status') ?? $request->query('collection_status');
        $prefId = $request->query('preference_id');
        if ($status !== 'approved' || !$prefId) {
            return redirect('/pago')->with('error','Pago no aprobado en Mercado Pago');
        }
        $orden = Orden::where('transaccion_id', $prefId)->where('usuario_id', optional($request->user())->id)->first();
        if (!$orden) { return redirect('/')->with('error','Orden no encontrada'); }
        $items = session('order_items_'.$prefId, []);
        $uploads = Upload::whereIn('id', collect($items)->pluck('id'))->get()->keyBy('id');
        DB::transaction(function() use ($orden, $items, $uploads) {
            foreach ($items as $it) {
                $upload = $uploads[$it['id']] ?? null;
                if (!$upload) { continue; }
                $qty = max(1, (int)($it['cantidad'] ?? 1));
                DetalleOrden::create([
                    'orden_id' => $orden->id,
                    'archivo_id' => $upload->id,
                    'precio_unitario' => $upload->stars_cost ?? 0,
                    'cantidad' => $qty,
                ]);
            }
            $orden->estado = 'aprobado';
            $orden->save();
        });
        session()->forget('order_items_'.$prefId);
        return redirect('/')->with('success','Pago confirmado. ¡Gracias!');
    }

    public function mpFailure(Request $request)
    {
        return redirect('/pago')->with('error','Pago cancelado o rechazado en Mercado Pago');
    }
}