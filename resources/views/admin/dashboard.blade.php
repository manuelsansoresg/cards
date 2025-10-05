@extends('admin.layout')

@section('content')
<div class="row g-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="h5">Usuarios</div>
                <div class="display-6">{{ $stats['users'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="h5">Tarjetas</div>
                <div class="display-6">{{ $stats['uploads'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="h5">Categorías</div>
                <div class="display-6">{{ $stats['categorias'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="h5">Órdenes</div>
                <div class="display-6">{{ $stats['ordenes'] }}</div>
            </div>
        </div>
    </div>
</div>
@endsection