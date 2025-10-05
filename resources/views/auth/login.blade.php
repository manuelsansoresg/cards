@extends('layouts.app')

@section('content')
<style>
    .auth-wrapper{min-height:calc(100vh - 64px);}
    .auth-card{background:rgba(255,255,255,.9);backdrop-filter:blur(8px);max-width:420px;border-radius:.75rem;border:1px solid rgba(0,0,0,.05)}
    .auth-title{font-weight:700}
    .auth-actions a{text-decoration:none}
    .form-floating>.form-control{border-radius:.5rem}
    .form-check-input{cursor:pointer}
</style>
<div class="auth-wrapper d-flex justify-content-center align-items-center">
    <div class="auth-card p-4 shadow-sm w-100">
        <div class="d-flex align-items-center mb-3">
            <i class="fas fa-user-circle fa-2x text-primary me-2"></i>
            <h5 class="auth-title mb-0">Inicia sesión</h5>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-floating mb-3">
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="correo@ejemplo.com">
                <label for="email">Correo electrónico</label>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-floating mb-3">
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Tu contraseña">
                <label for="password">Contraseña</label>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">Recordarme</label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="small">¿Olvidaste tu contraseña?</a>
                @endif
            </div>

            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>

        <div class="text-center mt-3 auth-actions">
            @if (Route::has('register'))
                <span class="text-muted small">¿No tienes cuenta?</span>
                <a href="{{ route('register') }}" class="ms-1">Crear cuenta</a>
            @endif
        </div>
    </div>
    </div>
</div>
@endsection
