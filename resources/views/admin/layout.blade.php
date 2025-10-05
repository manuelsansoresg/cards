<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 5 -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
    <!-- App CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <?php $starsSetting = \App\Models\StarsSetting::first(); ?>
    <header class="front-header d-flex align-items-center justify-content-between px-3">
        <a href="{{ url('/') }}" class="d-flex align-items-center gap-2 text-decoration-none text-white" title="Ir al inicio">
            @if(!empty($starsSetting->header_image))
                <img src="/{{ $starsSetting->header_image }}" alt="Header" class="avatar-placeholder" style="object-fit:cover;">
            @else
                <div class="avatar-placeholder"></div>
            @endif
            <div>
                <div class="fw-bold small">{{ $starsSetting->header_title ?? 'Admin Panel' }}</div>
                <div class="text-white-50 small">{{ auth()->user()->name ?? 'Admin' }}</div>
            </div>
        </a>
        <div class="d-flex align-items-center gap-3">
            <div class="dropdown">
                <a href="#" class="text-white position-relative" id="cartIconAdmin" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-shopping-cart"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form-admin').submit();">
                            <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
                        </a>
                        <form id="logout-form-admin" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                    </li>
                </ul>
            </div>
            <a href="{{ route('logout') }}" class="text-white" title="Salir"
               onclick="event.preventDefault(); document.getElementById('logout-form-admin').submit();">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </header>

    <nav id="adminNav" class="bg-light border-bottom">
        <div class="container py-2">
            <a href="{{ route('admin.dashboard') }}" class="me-3">Dashboard</a>
            <a href="{{ route('admin.users.index') }}" class="me-3">Usuarios</a>
            <a href="{{ route('admin.settings.edit') }}" class="me-3">Configuración</a>
            <a href="{{ route('admin.categorias.index') }}" class="me-3">Categorías</a>
            <a href="{{ route('admin.uploads.index') }}" class="me-3">Tarjetas</a>
            <a href="{{ route('admin.ordenes.index') }}" class="me-3">Órdenes</a>
            <a href="/" class="me-3">Home</a>
        </div>
    </nav>

    <main class="container my-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>