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
    <style>
        /* Ajuste móvil: cards más estrechas y alineadas a la izquierda.
           Si es collage (más de 1 media), darle más ancho para que se vea bien. */
        @media (max-width: 576px){
            #cards-grid .card-column{ display:flex; justify-content:flex-start; }
            /* Por defecto (single): más estrecho */
            #cards-grid .card-column .upload-card{ width:75%; max-width:380px; }
            /* Collage: si la card contiene .media-container no-single, darle más ancho */
            #cards-grid .card-column:has(.media-container:not(.single)) .upload-card{ width:92%; max-width:480px; }
        }
    </style>
</head>
<body class="front-bg">
    <header class="front-header d-flex align-items-center justify-content-between px-3">
        <div class="d-flex align-items-center gap-2">
            @if(!empty(optional(\App\Models\StarsSetting::first())->header_image))
                <img src="/{{ optional(\App\Models\StarsSetting::first())->header_image }}" alt="Header" class="avatar-placeholder" style="object-fit:cover;">
            @else
                <div class="avatar-placeholder"></div>
            @endif
            <div>
                <div class="fw-bold small">{{ optional(\App\Models\StarsSetting::first())->header_title ?? 'Idols Kpop' }}</div>
                <div class="text-white-50 small">{{ auth()->check() ? auth()->user()->name : 'Invitado' }}</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="dropdown">
                <a href="#" class="text-white position-relative" id="cartIcon" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-badge d-none" id="cartBadge">0</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" id="openCartMenuItem"><i class="fas fa-shopping-cart me-2"></i>Ver carrito</a></li>
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form-front').submit();">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
                            </a>
                            <form id="logout-form-front" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                        </li>
                    @else
                        <li><a class="dropdown-item" href="{{ route('login') }}"><i class="fas fa-user me-2"></i>Iniciar sesión</a></li>
                        @if (Route::has('register'))
                            <li><a class="dropdown-item" href="{{ route('register') }}"><i class="fas fa-user-plus me-2"></i>Registrarse</a></li>
                        @endif
                    @endauth
                </ul>
            </div>
            @auth
                @if((isset(auth()->user()->role) && auth()->user()->role === 'admin') || (method_exists(auth()->user(),'is_admin') && auth()->user()->is_admin))
                    <a href="{{ route('admin.dashboard') }}" class="text-white" title="Dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                    </a>
                @endif
            @endauth
            @auth
                <a href="{{ route('logout') }}" class="text-white" title="Salir"
                   onclick="event.preventDefault(); document.getElementById('logout-form-front').submit();">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            @endauth
            @guest
                <a href="{{ route('login') }}" class="text-white" title="Iniciar sesión"><i class="fas fa-user"></i></a>
            @endguest
        </div>
    </header>

    <div class="container py-3">
        <div class="d-flex flex-wrap gap-2 mb-3 category-bar">
            @foreach($categorias as $idx => $cat)
                <button class="btn btn-light btn-sm category-btn {{ $idx === 0 ? 'active' : '' }}" data-category="{{ $cat->id }}">{{ $cat->nombre }}</button>
            @endforeach
        </div>

        <!-- Modal selector completo de emojis -->
        <div class="modal fade" id="emojiPickerModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Selecciona un emoji</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(['❤','😍','🔥','👍','😘','🤗','⭐','👄','🤤','🙏','🛒','😱','😏','😇','🤒','🥰','🤩','🥳','🥹','🥴','😋','🤤','🤔','🫡','🙁','🫶','✌','🤟','🤝','💅','🍌','🍳','🍙','🍷','🛸','🎀','🏹','🪭','🥵','😖','😩','😫','🥸','🤑','👻','🌝','🎃','🙈','💦'] as $e)
                                <span class="emoji-option" data-emoji="{{ $e }}" style="font-size:1.5rem;cursor:pointer;">{{ $e }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
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
                                @if(\Illuminate\Support\Str::startsWith($media[0]->file_type, 'image'))
                                    <img src="/{{ $media[0]->file_path }}" alt="Imagen" class="media-img">
                                @else
                                    <video class="media-video" muted preload="metadata" playsinline>
                                        <source src="/{{ $media[0]->file_path }}">
                                    </video>
                                @endif
                                <span class="type-indicator"><i class="fas {{ \Illuminate\Support\Str::startsWith($media[0]->file_type,'image') ? 'fa-image' : 'fa-video' }}"></i></span>
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
                            <div class="card-title clamp-3">{{ $upload->title }}</div>
                            <div class="d-flex align-items-center justify-content-between mt-2">
                                <div class="reactions" id="reactions-{{ $upload->id }}">
                                    @foreach((($upload->reactions ?? collect())->groupBy('reaction')->map->count()->sortDesc()) as $emoji => $count)
                                        <span class="reaction">{{ $emoji }} <span class="badge bg-light text-dark">{{ $count }}</span></span>
                                    @endforeach
                                </div>
                                @auth
                                    @if($unlocked)
                                        <button class="btn btn-outline-dark btn-sm reaction-btn" data-upload-id="{{ $upload->id }}">
                                            Me gusta
                                        </button>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Modal/visor de medios -->
        <div class="modal fade" id="mediaViewer" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-sm-down">
                <div class="modal-content bg-dark text-white">
                    <div class="modal-header border-0">
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
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

        <style>
            .reaction-popover { position: absolute; z-index: 1050; background:#fff; border:1px solid #ddd; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,.15); padding:.4rem .5rem; display:none; }
            .reaction-popover .emoji { font-size: 1.35rem; padding:.2rem; cursor:pointer; }
            .reaction-popover .emoji:hover { transform: scale(1.1); }
            .reaction-popover .more { cursor:pointer; color:#333; font-weight:bold; margin-left:.25rem; }
        </style>

        <script>
            window.STARS_PER_DOLLAR = {{ optional(\App\Models\StarsSetting::first())->stars_per_dollar ?? 1 }};
            window.IS_AUTH = {{ auth()->check() ? 'true' : 'false' }};
            window.REACTION_EMOJIS = ['❤','😍','🔥','👍','😘','🤗','⭐','👄','🤤','🙏','🛒','😱','😏','😇','🤒','🥰','🤩','🥳','🥹','🥴','😋','🤤','🤔','🫡','🙁','🫶','✌','🤟','🤝','💅','🍌','🍳','🍙','🍷','🛸','🎀','🏹','🪭','🥵','😖','😩','😫','🥸','🤑','👻','🌝','🎃','🙈','💦'];
        </script>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        (function(){
            const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            let reactionPopoverEl;
            function ensurePopover(){
                if (reactionPopoverEl) return reactionPopoverEl;
                reactionPopoverEl = document.createElement('div');
                reactionPopoverEl.className = 'reaction-popover';
                document.body.appendChild(reactionPopoverEl);
                return reactionPopoverEl;
            }
            function positionPopover(el, anchor){
                const rect = anchor.getBoundingClientRect();
                const top = window.scrollY + rect.top - 10;
                const left = window.scrollX + rect.left + rect.width/2;
                el.style.top = `${top}px`;
                el.style.left = `${left - el.offsetWidth/2}px`;
            }
            function renderShortEmojis(uploadId){
                const list = (window.REACTION_EMOJIS || ['❤','😍','🔥','👍','😘']).slice(0,5);
                const moreBtn = '<span class="more">▾</span>';
                return list.map(e => `<span class="emoji" data-upload-id="${uploadId}" data-emoji="${e}">${e}</span>`).join('') + moreBtn;
            }
            function openPopover(anchor){
                const uploadId = Number(anchor.dataset.uploadId);
                if (!uploadId) return;
                const el = ensurePopover();
                el.innerHTML = renderShortEmojis(uploadId);
                el.style.display = 'block';
                positionPopover(el, anchor); positionPopover(el, anchor);
            }
            function closePopover(){ if (reactionPopoverEl) reactionPopoverEl.style.display = 'none'; }
            async function refreshReactions(uploadId){
                try {
                    const res = await fetch(`/reactions/${uploadId}`);
                    if (!res.ok) return;
                    const data = await res.json();
                    const cont = document.getElementById(`reactions-${uploadId}`);
                    if (!cont) return;
                    const counts = {};
                    (data.reactions || []).forEach(function(r){ counts[r] = (counts[r] || 0) + 1; });
                    cont.innerHTML = Object.keys(counts).sort((a,b)=>counts[b]-counts[a]).map(function(emoji){
                        const c = counts[emoji];
                        return `<span class="reaction">${emoji} <span class="badge bg-light text-dark">${c}</span></span>`;
                    }).join('');
                } catch (err) {}
            }
            async function saveReaction(uploadId, emoji){
                if (!window.IS_AUTH) return;
                try {
                    const res = await fetch('/reactions', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify({ upload_id: uploadId, reaction: emoji })
                    });
                    if (res.ok) await refreshReactions(uploadId);
                } catch (err) {}
            }
            // Abrir popover por click
            document.addEventListener('click', function(e){
                const btn = e.target.closest('.reaction-btn');
                if (btn) openPopover(btn);
                const inside = e.target.closest('.reaction-popover');
                if (!btn && !inside) closePopover();
            });
            // Elegir emoji o abrir listado completo
            document.addEventListener('click', function(e){
                const emojiEl = e.target.closest('.reaction-popover .emoji');
                if (emojiEl){
                    const id = Number(emojiEl.dataset.uploadId);
                    const emoji = emojiEl.dataset.emoji;
                    closePopover();
                    saveReaction(id, emoji);
                    return;
                }
                const moreEl = e.target.closest('.reaction-popover .more');
                if (moreEl){
                    const picker = document.getElementById('emojiPickerModal');
                    if (picker && window.bootstrap){
                        const modal = new window.bootstrap.Modal(picker);
                        picker.dataset.uploadId = String(document.querySelector('.reaction-popover .emoji')?.dataset.uploadId || '');
                        modal.show();
                    }
                }
            });
            // Long press en móvil
            let pressTimer = null;
            document.addEventListener('touchstart', function(e){
                const btn = e.target.closest('.reaction-btn');
                if (!btn) return;
                pressTimer = setTimeout(function(){ openPopover(btn); }, 400);
            });
            document.addEventListener('touchend', function(){ if (pressTimer){ clearTimeout(pressTimer); pressTimer = null; } });
            // Selección desde modal completo
            document.addEventListener('click', function(e){
                const fullEmoji = e.target.closest('#emojiPickerModal .emoji-option');
                if (!fullEmoji) return;
                const picker = document.getElementById('emojiPickerModal');
                const uploadId = Number(picker?.dataset.uploadId || '0');
                const emoji = fullEmoji.dataset.emoji;
                if (window.bootstrap && picker) {
                    const m = window.bootstrap.Modal.getInstance(picker);
                    m && m.hide();
                }
                saveReaction(uploadId, emoji);
            });

            // Abrir carrito desde el menú del icono
            const openCartItem = document.getElementById('openCartMenuItem');
            if (openCartItem) {
                openCartItem.addEventListener('click', function(e){
                    e.preventDefault();
                    const modalEl = document.getElementById('cartModal');
                    if (modalEl && window.bootstrap) {
                        const m = new window.bootstrap.Modal(modalEl);
                        m.show();
                    }
                });
            }
        })();
    </script>
</body>
</html>