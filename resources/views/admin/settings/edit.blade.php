@extends('admin.layout')

@section('content')
<h4>Configuración</h4>

<form method="POST" action="{{ route('admin.settings.update') }}" class="mt-3" enctype="multipart/form-data">
    @csrf
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Stars por dólar</label>
            <input type="number" step="0.01" name="stars_per_dollar" class="form-control" value="{{ old('stars_per_dollar', $setting->stars_per_dollar) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Paypal Client ID</label>
            <input type="text" name="paypal_client_id" class="form-control" value="{{ old('paypal_client_id', $setting->paypal_client_id) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Paypal Secret</label>
            <input type="text" name="paypal_secret" class="form-control" value="{{ old('paypal_secret', $setting->paypal_secret) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Paypal Mode</label>
            <input type="text" name="paypal_mode" class="form-control" value="{{ old('paypal_mode', $setting->paypal_mode) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Título del header</label>
            <input type="text" name="header_title" class="form-control" value="{{ old('header_title', $setting->header_title) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Imagen del header</label>
            <input type="file" name="header_image" class="form-control" accept="image/*">
            @if(!empty($setting->header_image))
                <div class="mt-2">
                    <img src="/{{ $setting->header_image }}" alt="Header Image" style="max-height:80px">
                </div>
            @endif
        </div>
        <div class="col-md-6">
            <label class="form-label">MercadoPago Public Key</label>
            <input type="text" name="mercadopago_public_key" class="form-control" value="{{ old('mercadopago_public_key', $setting->mercadopago_public_key) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">MercadoPago Access Token</label>
            <input type="text" name="mercadopago_access_token" class="form-control" value="{{ old('mercadopago_access_token', $setting->mercadopago_access_token) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">MercadoPago Mode</label>
            <input type="text" name="mercadopago_mode" class="form-control" value="{{ old('mercadopago_mode', $setting->mercadopago_mode) }}">
        </div>
    </div>
    <div class="mt-3">
        <button class="btn btn-success" type="submit">Guardar</button>
    </div>
</form>
@endsection