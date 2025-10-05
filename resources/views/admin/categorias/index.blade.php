@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Categorías</h4>
    <a class="btn btn-primary" href="{{ route('admin.categorias.create') }}">Nueva categoría</a>
    </div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($categorias as $categoria)
            <tr>
                <td>{{ $categoria->id }}</td>
                <td>{{ $categoria->nombre }}</td>
                <td>{{ $categoria->estado ? 'Activo' : 'Inactivo' }}</td>
                <td>
                    <a href="{{ route('admin.categorias.edit', $categoria) }}" class="btn btn-sm btn-primary">Editar</a>
                    <form action="{{ route('admin.categorias.destroy', $categoria) }}" method="POST" class="d-inline-block delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $categorias->links() }}
@endsection