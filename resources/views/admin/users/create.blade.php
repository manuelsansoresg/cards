@extends('admin.layout')

@section('content')
<h4>Crear usuario</h4>

<form method="POST" action="{{ route('admin.users.store') }}" class="mt-3">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nombre</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Role</label>
            <select name="role" class="form-select" required>
                <option value="user" {{ old('role')==='user' ? 'selected' : '' }}>user</option>
                <option value="admin" {{ old('role')==='admin' ? 'selected' : '' }}>admin</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Stars</label>
            <input type="number" name="stars" class="form-control" value="{{ old('stars', 0) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
    </div>
    <div class="mt-3">
        <button class="btn btn-success" type="submit">Guardar</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</form>
@endsection