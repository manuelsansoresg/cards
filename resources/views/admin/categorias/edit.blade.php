@extends('admin.layout')

@section('content')
<h4>Editar categoría #{{ $categoria->id }}</h4>

<form method="POST" action="{{ route('admin.categorias.update', $categoria) }}" class="mt-3">
    @csrf
    @method('PUT')
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $categoria->nombre) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
                <option value="1" {{ $categoria->estado ? 'selected' : '' }}>Activo</option>
                <option value="0" {{ !$categoria->estado ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>
    </div>
    <div class="mt-3">
        <button class="btn btn-success" type="submit">Guardar</button>
        <a href="{{ route('admin.categorias.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</form>
@endsection