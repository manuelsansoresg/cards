<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Método de Pago</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="front-bg">
    <div class="container py-4">
        <div class="text-center mb-4">
            <h5 class="fw-bold"><i class="fas fa-credit-card me-2"></i>Seleccionar Método de Pago</h5>
            <div class="text-muted">Elige cómo deseas completar tu compra</div>
        </div>

        <div class="row g-3 justify-content-center">
            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-header"><i class="fas fa-shopping-basket me-2"></i>Resumen de tu compra</div>
                    <div class="card-body" id="summaryItems">
                        <div class="text-muted">Cargando carrito…</div>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <div class="fw-bold">Total:</div>
                        <div>
                            <span id="sumTotalStars">0</span> <i class="fas fa-star text-warning"></i>
                            <span class="text-muted small">(≈ <span id="sumTotalUsd">$0.00</span> USD)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-header">Métodos de pago disponibles</div>
                    <div class="card-body">
                        <div class="list-group">
                            <label class="list-group-item d-flex align-items-center">
                                <input class="form-check-input me-2" type="radio" name="paymentMethod" value="paypal">
                                <img src="/uploads/paypal.png" alt="PayPal" style="height:28px" class="me-2">
                                <div>
                                    <div class="fw-bold">PayPal</div>
                                    <div class="small text-muted">Pago seguro con PayPal</div>
                                </div>
                            </label>
                            <label class="list-group-item d-flex align-items-center">
                                <input class="form-check-input me-2" type="radio" name="paymentMethod" value="mercadopago">
                                <img src="/uploads/mercado_pago.png" alt="Mercado Pago" style="height:28px" class="me-2">
                                <div>
                                    <div class="fw-bold">MercadoPago</div>
                                    <div class="small text-muted">Tarjetas, efectivo y más</div>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="/" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>
                        <button class="btn btn-primary" id="continuePayBtn"><i class="fas fa-arrow-right me-1"></i>Continuar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.STARS_PER_DOLLAR = {{ optional(\App\Models\StarsSetting::first())->stars_per_dollar ?? 1 }};
        window.IS_AUTH = {{ auth()->check() ? 'true' : 'false' }};

        const summaryItemsEl = document.getElementById('summaryItems');
        const totalStarsEl = document.getElementById('sumTotalStars');
        const totalUsdEl = document.getElementById('sumTotalUsd');
        function loadCart(){
            let cart = [];
            try { cart = JSON.parse(localStorage.getItem('cart')||'[]'); } catch (e) { cart = []; }
            if (!cart.length){
                summaryItemsEl.innerHTML = '<div class="text-muted">Tu carrito está vacío.</div>';
            } else {
                summaryItemsEl.innerHTML = cart.map(function(it){
                    return `<div class="d-flex justify-content-between border-bottom py-2">
                        <div>
                            <div class="fw-bold">${it.title}</div>
                            <div class="small text-muted">Archivo digital</div>
                        </div>
                        <div><i class="fas fa-star text-warning"></i> ${it.stars}</div>
                    </div>`;
                }).join('');
            }
            const stars = cart.reduce((a,it)=>a + (it.stars * (it.cantidad||1)), 0);
            const usd = stars / (window.STARS_PER_DOLLAR || 1);
            totalStarsEl.textContent = String(stars);
            totalUsdEl.textContent = `$${usd.toFixed(2)}`;
        }
        loadCart();

        document.getElementById('continuePayBtn').addEventListener('click', function(){
            const method = document.querySelector('input[name="paymentMethod"]:checked')?.value;
            if (!method) { alert('Selecciona un método de pago'); return; }
            if (!window.IS_AUTH){ window.location.href = '/login'; return; }
            let cart = [];
            try { cart = JSON.parse(localStorage.getItem('cart')||'[]'); } catch (e) { cart = []; }
            if (!cart.length){ alert('Tu carrito está vacío'); return; }
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('/checkout', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ items: cart.map(it => ({ id: it.id, cantidad: it.cantidad||1 })), metodo: method })
            }).then(r => r.json()).then(resp => {
                if (resp && resp.success && resp.redirect){
                    // Redirige a PayPal/MercadoPago para aprobar el pago
                    window.location.href = resp.redirect;
                } else {
                    alert(resp.message || 'No se pudo iniciar el pago');
                }
            }).catch(err => {
                alert('Error de red al procesar el pago');
                console.error(err);
            });
        });
    </script>
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>