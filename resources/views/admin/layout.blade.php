<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- App CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <?php $starsSetting = \App\Models\StarsSetting::first(); ?>
    <header class="admin-header d-flex align-items-center px-3">
        <div id="adminMenuToggle" class="me-3 fs-4" role="button" aria-label="Toggle navigation">&#9776;</div>
        <div class="text-white fw-bold">{{ $starsSetting->header_title ?? 'Admin Panel' }}</div>
        @if(!empty($starsSetting->header_image))
            <img src="/{{ $starsSetting->header_image }}" alt="Header" class="ms-3" style="height:32px;object-fit:contain">
        @endif
        <div class="ms-auto d-flex align-items-center gap-3">
            <a href="#" class="text-white text-decoration-none"
               onclick="event.preventDefault(); document.getElementById('logout-form-admin').submit();">Cerrar sesión</a>
            <form id="logout-form-admin" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
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
    <script>
        // Toggle del menú hamburguesa
        (function(){
            const toggle = document.getElementById('adminMenuToggle');
            const nav = document.getElementById('adminNav');
            if (toggle && nav) {
                toggle.addEventListener('click', function(){
                    nav.classList.toggle('d-none');
                });
            }
        })();
    </script>
</body>
</html>