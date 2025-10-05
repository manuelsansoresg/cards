<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StarsSetting;
use GuzzleHttp\Client as GuzzleClient;

class PaypalAuthCheck extends Command
{
    /** @var string */
    protected $signature = 'paypal:check-auth';

    /** @var string */
    protected $description = 'Verifica las credenciales de PayPal (sandbox/live) obteniendo un access token';

    public function handle(): int
    {
        $cfg = StarsSetting::first();
        if (!$cfg || !$cfg->paypal_client_id || !$cfg->paypal_secret) {
            $this->error('Faltan credenciales en stars_settings (paypal_client_id / paypal_secret).');
            return 1;
        }

        $mode = strtolower((string)($cfg->paypal_mode ?? 'sandbox'));
        $base = $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
        $this->info('Probando modo: '.$mode.' en '.$base);

        try {
            $client = new GuzzleClient(['base_uri' => $base]);
            $resp = $client->post('/v1/oauth2/token', [
                'auth' => [$cfg->paypal_client_id, $cfg->paypal_secret],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => ['grant_type' => 'client_credentials'],
                'http_errors' => false,
            ]);
            $status = $resp->getStatusCode();
            $body = (string)$resp->getBody();
            if ($status >= 200 && $status < 300) {
                $data = json_decode($body, true);
                $this->info('OK: access_token recibido, expires_in='.$data['expires_in'] ?? '');
                return 0;
            }
            $this->error('Fallo OAuth (status '.$status.'): '.$body);
            $this->line('Sugerencias: verificar client_id/secret y que paypal_mode coincida con las credenciales (sandbox/live).');
            return 1;
        } catch (\Throwable $e) {
            $this->error('Error al conectar: '.$e->getMessage());
            return 1;
        }
    }
}