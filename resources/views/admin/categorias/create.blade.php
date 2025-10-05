@extends('admin.layout')

@section('content')
<h4>Nueva categoría</h4>

<form method="POST" action="{{ route('admin.categorias.store') }}" class="mt-3">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
        </div>
    </div>
    <div class="mt-3">
        <button class="btn btn-success" type="submit">Crear</button>
        <a href="{{ route('admin.categorias.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</form>
@endsection