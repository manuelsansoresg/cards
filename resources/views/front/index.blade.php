<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página principal</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- FontAwesome 5 -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
    <!-- App CSS (Bootstrap 5 incluido vía SCSS) -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="front-bg">
    <header class="front-header d-flex align-items-center justify-content-between px-3">
        <div class="d-flex align-items-center gap-2">
            <div class="avatar-placeholder"></div>
            <div>
                <div class="fw-bold small">Idols Kpop</div>
                <div class="text-white-50 small">Invitado</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="#" class="text-white"><i class="fas fa-bell"></i></a>
            <a href="#" class="text-white" id="cartIcon"><i class="fas fa-shopping-cart"></i><span class="cart-badge d-none" id="cartBadge">0</span></a>
            @guest
                <a href="{{ route('login') }}" class="text-white"><i class="fas fa-user"></i></a>
            @else
                <a href="{{ route('home') }}" class="text-white"><i class="fas fa-user-check"></i></a>
            @endguest
        </div>
    </header>

    <div class="container py-3">
        <div class="d-flex flex-wrap gap-2 mb-3 category-bar">
            @foreach($categorias as $idx => $cat)
                <button class="btn btn-light btn-sm category-btn {{ $idx === 0 ? 'active' : '' }}" data-category="{{ $cat->id }}">{{ $cat->nombre }}</button>
            @endforeach
        </div>

        <div class="row g-3" id="cards-grid">
            @foreach($uploads as $upload)
                @php
                    $unlocked = $upload->is_free || (isset($purchasedUploadIds) && in_array($upload->id, $purchasedUploadIds));
                @endphp
                <div class="col-12 col-sm-6 col-lg-3 card-column" data-category="{{ $upload->categoria_id }}">
                    <div class="upload-card {{ $unlocked ? '' : 'locked' }}">
                        @php
                            $media = $upload->media->sortBy('sort_order')->values();
                            $count = $media->count();
                            $singleClass = $count === 1 ? ' single' : '';
                        @endphp
                        <div class="media-container position-relative{{ $singleClass }}" data-clickable="{{ $unlocked ? 'true' : 'false' }}">
                            @if($count === 0)
                                <div class="placeholder d-flex align-items-center justify-content-center w-100 h-100">
                                    <i class="fas fa-photo-video fa-2x text-muted"></i>
                                </div>
                            @elseif($count === 1)
                                @php $m = $media[0]; @endphp
                                @if(\Illuminate\Support\Str::startsWith($m->file_type, 'image'))
                                    <img src="/{{ $m->file_path }}" alt="Imagen" class="media-img">
                                @else
                                    <video class="media-video" muted preload="metadata" playsinline>
                                        <source src="/{{ $m->file_path }}">
                                    </video>
                                @endif
                                <span class="type-indicator"><i class="fas {{ \Illuminate\Support\Str::startsWith($m->file_type,'image') ? 'fa-image' : 'fa-video' }}"></i></span>
                            @else
                                <div class="collage">
                                    @foreach($media->take(4) as $m)
                                        <div class="collage-item">
                                            @if(\Illuminate\Support\Str::startsWith($m->file_type, 'image'))
                                                <img src="/{{ $m->file_path }}" alt="Imagen" class="media-img">
                                            @else
                                                <video class="media-video" muted preload="metadata">
                                                    <source src="/{{ $m->file_path }}">
                                                </video>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <span class="type-indicator"><i class="fas fa-photo-video"></i></span>
                            @endif

                            @if(!$unlocked)
                                <div class="locked-overlay d-flex flex-column align-items-center justify-content-center text-center">
                                    <div class="lock-text">Bloqueado <i class="fas fa-lock"></i> {{ $upload->stars_cost }} estrellas</div>
                                    <button class="btn btn-primary btn-sm mt-2 add-to-cart"
                                        data-id="{{ $upload->id }}"
                                        data-title="{{ $upload->title }}"
                                        data-stars="{{ $upload->stars_cost }}">
                                        <i class="fas fa-cart-plus"></i> Agregar al carrito
                                    </button>
                                </div>
                            @endif

                            <div class="media-list d-none">
                                @foreach($media as $m)
                                    <span class="media-item" data-type="{{ \Illuminate\Support\Str::startsWith($m->file_type, 'image') ? 'image' : 'video' }}" data-src="/{{ $m->file_path }}" data-mime="{{ $m->file_type }}"></span>
                                @endforeach
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="card-title clamp-2">{{ $upload->title }}</div>
                            <div class="reactions mt-2">
                                @foreach(($upload->reactions ?? collect())->take(6) as $reaction)
                                    <span class="reaction">{{ $reaction->reaction }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Modal/visor de medios -->
        <div class="modal fade" id="mediaViewer" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content bg-dark text-white">
                    <div class="modal-body p-0 position-relative">
                        <button type="button" class="btn btn-light position-absolute top-0 end-0 m-2" data-bs-dismiss="modal" aria-label="Close"><i class="fas fa-times"></i></button>
                        <div id="viewerSlides" class="w-100" style="min-height:60vh;background:#000;display:flex;align-items:center;justify-content:center"></div>
                        <div class="d-flex justify-content-between align-items-center p-2">
                            <button class="btn btn-secondary btn-sm" id="prevSlide"><i class="fas fa-chevron-left"></i></button>
                            <button class="btn btn-secondary btn-sm" id="nextSlide"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Carrito -->
        <div class="modal fade" id="cartModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-shopping-cart me-2"></i>Carrito</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="cartItems"></div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="fw-bold">Total: <span id="cartTotalStars">0</span> <i class="fas fa-star text-warning"></i></div>
                            <div class="text-muted small">≈ <span id="cartTotalUsd">$0.00</span> USD</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Seguir viendo</button>
                        <button class="btn btn-primary" id="checkoutBtn">Pagar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Checkout -->
        <div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Seleccionar Método de Pago</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="list-group">
                            <label class="list-group-item">
                                <input class="form-check-input me-1" type="radio" name="paymentMethod" value="paypal"> PayPal
                            </label>
                            <label class="list-group-item">
                                <input class="form-check-input me-1" type="radio" name="paymentMethod" value="mercadopago"> MercadoPago
                            </label>
                        </div>
                        <div class="mt-3 text-muted small">Total: <span id="checkoutTotalStars">0</span> <i class="fas fa-star text-warning"></i> (≈ <span id="checkoutTotalUsd">$0.00</span> USD)</div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Volver</button>
                        <button class="btn btn-success" id="confirmCheckoutBtn">Continuar</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            window.STARS_PER_DOLLAR = {{ optional(\App\Models\StarsSetting::first())->stars_per_dollar ?? 1 }};
            window.IS_AUTH = {{ auth()->check() ? 'true' : 'false' }};
        </script>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>