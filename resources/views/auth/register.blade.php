@extends('layouts.app')

@section('content')
<style>
    .auth-wrapper{min-height:calc(100vh - 64px);}
    .auth-card{background:rgba(255,255,255,.9);backdrop-filter:blur(8px);max-width:480px;border-radius:.75rem;border:1px solid rgba(0,0,0,.05)}
    .auth-title{font-weight:700}
    .auth-actions a{text-decoration:none}
    .form-floating>.form-control{border-radius:.5rem}
</style>
<div class="auth-wrapper d-flex justify-content-center align-items-center">
    <div class="auth-card p-4 shadow-sm w-100">
        <div class="mb-2">
            <a href="/" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
        <div class="d-flex align-items-center mb-3">
            <i class="fas fa-user-plus fa-2x text-primary me-2"></i>
            <h5 class="auth-title mb-0">Crear cuenta</h5>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-floating mb-3">
                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Tu nombre">
                <label for="name">Nombre</label>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-floating mb-3">
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="correo@ejemplo.com">
                <label for="email">Correo electrónico</label>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-floating mb-3">
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Contraseña">
                <label for="password">Contraseña</label>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-floating mb-3">
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Confirmación">
                <label for="password-confirm">Confirmar contraseña</label>
            </div>

            <button type="submit" class="btn btn-primary w-100">Registrarse</button>
        </form>

        <div class="text-center mt-3 auth-actions">
            <span class="text-muted small">¿Ya tienes cuenta?</span>
            <a href="{{ route('login') }}" class="ms-1">Iniciar sesión</a>
        </div>
    </div>
</div>
@endsection
