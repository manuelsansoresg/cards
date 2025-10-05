@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Tarjetas</h4>
    <a class="btn btn-primary" href="{{ route('admin.uploads.create') }}">Nueva tarjeta</a>
    </div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Precio</th>
            <th>Stars</th>
            <th>Categoría</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($uploads as $upload)
            <tr>
                <td>{{ $upload->id }}</td>
                <td>{{ $upload->title }}</td>
                <td>{{ $upload->price }}</td>
                <td>{{ $upload->stars_cost }}</td>
                <td>{{ optional($upload->categoria)->nombre }}</td>
                <td>
                    <a href="{{ route('admin.uploads.edit', $upload) }}" class="btn btn-sm btn-primary">Editar</a>
                    <form action="{{ route('admin.uploads.destroy', $upload) }}" method="POST" class="d-inline-block delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $uploads->links() }}
@endsection