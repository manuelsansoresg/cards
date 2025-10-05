@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Órdenes</h4>
</div>

<form method="GET" class="row g-3 mb-4">
    <div class="col-md-3">
        <label class="form-label">Desde</label>
        <input type="date" name="desde" value="{{ request('desde') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Hasta</label>
        <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Método de pago</label>
        <input type="text" name="metodo_pago" value="{{ request('metodo_pago') }}" class="form-control" placeholder="paypal, mercado, etc">
    </div>
    <div class="col-md-3">
        <label class="form-label">Estado</label>
        <input type="text" name="estado" value="{{ request('estado') }}" class="form-control" placeholder="paid, pending, etc">
    </div>
    <div class="col-md-12">
        <button class="btn btn-primary" type="submit">Filtrar</button>
        <a href="{{ route('admin.ordenes.index') }}" class="btn btn-secondary">Limpiar</a>
    </div>
</form>

<div class="alert alert-info">Ganancias: <strong>{{ number_format($ganancias, 2) }}</strong></div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Método</th>
            <th>Estado</th>
            <th>Total</th>
            <th>Fecha</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ordenes as $orden)
            <tr>
                <td>{{ $orden->id }}</td>
                <td>{{ $orden->email ?? $orden->usuario_id }}</td>
                <td>{{ $orden->metodo_pago }}</td>
                <td>{{ $orden->estado }}</td>
                <td>{{ number_format($orden->total_monto, 2) }}</td>
                <td>{{ $orden->created_at }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $ordenes->links() }}
@endsection