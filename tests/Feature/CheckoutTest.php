<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Upload;
use App\Models\Categoria;
use App\Models\StarsSetting;
use App\Http\Controllers\CheckoutController;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed minimal settings
        StarsSetting::query()->create([
            'stars_per_dollar' => 20,
            'paypal_client_id' => 'test-client-id',
            'paypal_secret' => 'test-secret',
            'paypal_mode' => 'sandbox',
            'header_title' => 'Test',
            'mercadopago_public_key' => 'TEST-public',
            'mercadopago_access_token' => 'TEST-access-token',
            'mercadopago_mode' => 'sandbox',
        ]);
    }

    protected function createUpload(int $stars = 30): Upload
    {
        $user = User::factory()->create();
        $categoria = Categoria::query()->create([
            'nombre' => 'General',
            'estado' => 'activo',
        ]);
        return Upload::query()->create([
            'user_id' => $user->id,
            'title' => 'Archivo digital',
            'price' => 1.50,
            'is_free' => false,
            'stars_cost' => $stars,
            'type' => 'image',
            'categoria_id' => $categoria->id,
        ]);
    }

    public function test_paypal_checkout_redirects_to_approval_url(): void
    {
        $user = User::factory()->create();
        $upload = $this->createUpload();

        // Fake controller: return a valid PayPal approval URL
        app()->bind(CheckoutController::class, function () {
            return new class extends CheckoutController {
                protected function processPayPal(float $amountUsd, string $returnUrl, string $cancelUrl, array $items): ?array
                {
                    return [
                        'approval_url' => 'https://www.sandbox.paypal.com/checkoutnow?token=ORDER123',
                        'order_id' => 'ORDER123',
                    ];
                }
            };
        });

        $payload = [
            'items' => [ ['id' => $upload->id, 'cantidad' => 1] ],
            'metodo' => 'paypal',
        ];

        $resp = $this->actingAs($user)->postJson('/checkout', $payload);
        $resp->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['redirect','orden_id']);
        $this->assertStringContainsString('sandbox.paypal.com/checkoutnow', $resp->json('redirect'));
    }

    public function test_mercadopago_checkout_redirects_to_init_point(): void
    {
        $user = User::factory()->create();
        $upload = $this->createUpload();

        // Fake controller: return a valid MercadoPago init_point
        app()->bind(CheckoutController::class, function () {
            return new class extends CheckoutController {
                protected function processMercadoPago(float $amountUsd, string $successUrl, string $failureUrl, array $items): ?array
                {
                    return [
                        'init_point' => 'https://sandbox.mercadopago.com/checkout/pay/PREF123',
                        'preference_id' => 'PREF123',
                    ];
                }
            };
        });

        $payload = [
            'items' => [ ['id' => $upload->id, 'cantidad' => 1] ],
            'metodo' => 'mercadopago',
        ];

        $resp = $this->actingAs($user)->postJson('/checkout', $payload);
        $resp->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['redirect','orden_id']);
        $this->assertStringContainsString('mercadopago.com/checkout/pay', $resp->json('redirect'));
    }

    public function test_paypal_checkout_reports_error_message(): void
    {
        $user = User::factory()->create();
        $upload = $this->createUpload();

        app()->bind(CheckoutController::class, function () {
            return new class extends CheckoutController {
                protected function processPayPal(float $amountUsd, string $returnUrl, string $cancelUrl, array $items): ?array
                {
                    return ['error' => 'AUTHENTICATION_FAILURE'];
                }
            };
        });

        $payload = [
            'items' => [ ['id' => $upload->id, 'cantidad' => 1] ],
            'metodo' => 'paypal',
        ];

        $resp = $this->actingAs($user)->postJson('/checkout', $payload);
        $resp->assertStatus(200)
            ->assertJson(['success' => false]);
        $this->assertStringContainsString('PayPal:', $resp->json('message'));
    }

    public function test_mercadopago_checkout_reports_error_message(): void
    {
        $user = User::factory()->create();
        $upload = $this->createUpload();

        app()->bind(CheckoutController::class, function () {
            return new class extends CheckoutController {
                protected function processMercadoPago(float $amountUsd, string $successUrl, string $failureUrl, array $items): ?array
                {
                    return ['error' => 'Preference creada sin init_point/id'];
                }
            };
        });

        $payload = [
            'items' => [ ['id' => $upload->id, 'cantidad' => 1] ],
            'metodo' => 'mercadopago',
        ];

        $resp = $this->actingAs($user)->postJson('/checkout', $payload);
        $resp->assertStatus(200)
            ->assertJson(['success' => false]);
        $this->assertStringContainsString('Mercado Pago:', $resp->json('message'));
    }
}