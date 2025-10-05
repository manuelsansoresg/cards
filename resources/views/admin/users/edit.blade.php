@extends('admin.layout')

@section('content')
<h4>Editar usuario #{{ $user->id }}</h4>

<form method="POST" action="{{ route('admin.users.update', $user) }}" class="mt-3">
    @csrf
    @method('PUT')
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nombre</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Role</label>
            <select name="role" class="form-select" required>
                <option value="user" {{ $user->role==='user' ? 'selected' : '' }}>user</option>
                <option value="admin" {{ $user->role==='admin' ? 'selected' : '' }}>admin</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Stars</label>
            <input type="number" name="stars" class="form-control" value="{{ old('stars', $user->stars) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label d-block">Estado</label>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="activo" id="activo_true" value="1" {{ old('activo', (int)$user->activo) == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="activo_true">Activo</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="activo" id="activo_false" value="0" {{ old('activo', (int)$user->activo) == 0 ? 'checked' : '' }}>
                <label class="form-check-label" for="activo_false">Inactivo</label>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Nueva contraseña (opcional)</label>
            <input type="password" name="password" class="form-control" placeholder="Ingresa sólo si deseas cambiarla">
        </div>
    </div>
    <div class="mt-3">
        <button class="btn btn-success" type="submit">Guardar</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</form>
@endsection