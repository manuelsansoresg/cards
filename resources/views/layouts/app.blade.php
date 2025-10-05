<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
    <!-- App CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!-- App JS -->
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body class="front-bg">
    <?php $starsSetting = \App\Models\StarsSetting::first(); ?>
    <header class="front-header d-flex align-items-center justify-content-between px-3">
        <div class="d-flex align-items-center gap-2">
            @if(!empty($starsSetting->header_image))
                <img src="/{{ $starsSetting->header_image }}" alt="Header" class="avatar-placeholder" style="object-fit:cover;">
            @else
                <div class="avatar-placeholder"></div>
            @endif
            <div>
                <div class="fw-bold small">{{ $starsSetting->header_title ?? config('app.name', 'Laravel') }}</div>
                <div class="text-white-50 small">{{ auth()->check() ? auth()->user()->name : 'Invitado' }}</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="dropdown">
                <a href="#" class="text-white position-relative" id="appHeaderMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-shopping-cart"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    @auth
                        @if((isset(auth()->user()->role) && auth()->user()->role === 'admin') || (method_exists(auth()->user(),'is_admin') && auth()->user()->is_admin))
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                            <li><hr class="dropdown-divider"></li>
                        @endif
                        <li>
                            <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form-app').submit();">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
                            </a>
                            <form id="logout-form-app" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
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
                   onclick="event.preventDefault(); document.getElementById('logout-form-app').submit();">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            @else
                <a href="{{ route('login') }}" class="text-white" title="Iniciar sesión"><i class="fas fa-user"></i></a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="text-white" title="Registrarse"><i class="fas fa-user-plus"></i></a>
                @endif
            @endauth
        </div>
    </header>

    <main class="container py-3">
        @yield('content')
    </main>
</body>
</html>
